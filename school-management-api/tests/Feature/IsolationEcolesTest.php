<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Vérifie que les données d'une école ne sont jamais visibles, modifiables
 * ni référençables depuis une autre école.
 */
class IsolationEcolesTest extends TestCase
{
    use RefreshDatabase;

    private User $adminA;

    private User $adminB;

    private Classe $classeA;

    private Classe $classeB;

    private Eleve $eleveB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminA = User::factory()->admin()->create();
        $this->adminB = User::factory()->admin()->create();

        $this->classeA = $this->makeClasse($this->adminA->ecole_id);
        $this->classeB = $this->makeClasse($this->adminB->ecole_id);

        $this->eleveB = Eleve::create([
            'ecole_id' => $this->adminB->ecole_id,
            'classe_id' => $this->classeB->id,
            'matricule' => 'B-0001',
            'nom' => 'SecretB',
            'prenom' => 'Eleve',
            'sexe' => 'M',
            'date_naissance' => '2012-01-01',
            'statut' => 'actif',
            'date_inscription' => '2025-09-15',
        ]);
    }

    public function test_une_ecole_ne_voit_pas_les_eleves_d_une_autre(): void
    {
        $this->actingAs($this->adminA);

        $this->assertSame(0, Eleve::count());
        $this->assertNull(Eleve::find($this->eleveB->id));
    }

    public function test_une_ecole_ne_peut_pas_modifier_un_eleve_d_une_autre(): void
    {
        $this->actingAs($this->adminA)
            ->get("/eleves/{$this->eleveB->id}/edit")
            ->assertNotFound();

        $this->actingAs($this->adminA)
            ->delete("/eleves/{$this->eleveB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('eleves', ['id' => $this->eleveB->id]);
    }

    public function test_une_ecole_ne_peut_pas_referencer_la_classe_d_une_autre(): void
    {
        $this->actingAs($this->adminA)->post('/eleves', [
            'matricule' => 'A-0001',
            'nom' => 'Intrus',
            'prenom' => 'Eleve',
            'sexe' => 'F',
            'date_naissance' => '2012-05-10',
            'classe_id' => $this->classeB->id,
            'statut' => 'actif',
            'date_inscription' => '2025-09-15',
        ])->assertSessionHasErrors('classe_id');

        $this->assertDatabaseMissing('eleves', ['matricule' => 'A-0001']);
    }

    public function test_deux_ecoles_peuvent_utiliser_le_meme_matricule_et_code(): void
    {
        $this->actingAs($this->adminA)->post('/eleves', [
            'matricule' => 'B-0001',
            'nom' => 'Homonyme',
            'prenom' => 'Eleve',
            'sexe' => 'F',
            'date_naissance' => '2012-05-10',
            'classe_id' => $this->classeA->id,
            'statut' => 'actif',
            'date_inscription' => '2025-09-15',
        ])->assertSessionHasNoErrors();

        foreach ([$this->adminA, $this->adminB] as $admin) {
            $this->actingAs($admin)->post('/matieres', [
                'nom' => 'Mathématiques',
                'code' => 'MATH',
                'coefficient_defaut' => 4,
            ])->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('matieres', 2);
    }

    public function test_un_admin_ne_peut_pas_toucher_aux_comptes_d_une_autre_ecole(): void
    {
        $parentB = User::factory()->parent()->create(['ecole_id' => $this->adminB->ecole_id]);
        $superAdmin = User::factory()->superAdmin()->create();

        foreach ([$parentB, $this->adminB, $superAdmin] as $cible) {
            $ancienMotDePasse = $cible->password;

            $this->actingAs($this->adminA)
                ->post("/comptes/{$cible->id}/reinitialiser-mot-de-passe", ['nouveau_mot_de_passe' => 'piratage'])
                ->assertForbidden();

            $this->actingAs($this->adminA)
                ->delete("/comptes/{$cible->id}")
                ->assertForbidden();

            $this->assertSame($ancienMotDePasse, $cible->fresh()->password);
        }
    }

    public function test_un_admin_gere_toujours_les_comptes_de_sa_propre_ecole(): void
    {
        $parentA = User::factory()->parent()->create(['ecole_id' => $this->adminA->ecole_id]);

        $this->actingAs($this->adminA)
            ->post("/comptes/{$parentA->id}/reinitialiser-mot-de-passe", ['nouveau_mot_de_passe' => 'nouveau123'])
            ->assertRedirect();
    }

    public function test_une_ecole_ne_peut_pas_payer_ni_voir_la_facture_d_une_autre(): void
    {
        $factureB = Facture::create([
            'ecole_id' => $this->adminB->ecole_id,
            'montant' => 25000,
            'date_echeance' => now()->addWeek(),
            'statut' => Facture::STATUT_EN_ATTENTE,
        ]);

        $this->actingAs($this->adminA);
        $this->assertNull(Facture::find($factureB->id));

        $this->get("/paydunya/initier/{$factureB->id}")->assertNotFound();
        $this->get("/paydunya/succes/{$factureB->id}?token=x")->assertNotFound();
    }

    public function test_api_mobile_isole_les_ecoles(): void
    {
        Sanctum::actingAs($this->adminA);

        $this->getJson("/api/classes/{$this->classeB->id}")->assertNotFound();
        $this->getJson("/api/eleves/{$this->eleveB->id}")->assertNotFound();
        $this->getJson('/api/eleves')->assertOk()->assertDontSee('SecretB');
    }

    public function test_le_super_admin_voit_toutes_les_ecoles(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->assertSame(2, Classe::count());
        $this->assertNotNull(Eleve::find($this->eleveB->id));
    }

    private function makeClasse(int $ecoleId): Classe
    {
        $annee = AnneeScolaire::create([
            'ecole_id' => $ecoleId,
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-15',
            'date_fin' => '2026-07-05',
            'active' => true,
        ]);

        return Classe::create([
            'ecole_id' => $ecoleId,
            'nom' => '6ème A',
            'niveau' => '6ème',
            'annee_scolaire_id' => $annee->id,
        ]);
    }
}
