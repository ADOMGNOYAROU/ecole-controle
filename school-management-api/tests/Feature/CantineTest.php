<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\PaiementCantine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CantineTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Eleve $ama;

    private Eleve $kofi;

    private Eleve $sena;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 10, 5)->setTime(12, 0));

        $this->admin = User::factory()->admin()->create();
        $classe = $this->makeClasse($this->admin->ecole_id);

        $this->ama = $this->makeEleve($classe, 'C-001', 'Ama');
        $this->kofi = $this->makeEleve($classe, 'C-002', 'Kofi');
        $this->sena = $this->makeEleve($classe, 'C-003', 'Sena');
    }

    public function test_parcours_quotidien_de_la_cantine(): void
    {
        // Réglages : prix du jour et élèves qui mangent à la cantine (Sena n'y mange pas)
        $this->actingAs($this->admin)->put('/cantine/parametres', [
            'prix_cantine' => 300,
            'eleves' => [$this->ama->id, $this->kofi->id],
        ])->assertRedirect('/cantine');

        $this->get('/cantine')->assertOk()
            ->assertSee('Ama')->assertSee('Kofi')->assertDontSee('Sena');

        // Le parent d'Ama paie : on la coche, elle sort de la liste
        $this->post("/cantine/eleves/{$this->ama->id}/payer")->assertRedirect('/cantine');

        $paiement = PaiementCantine::sole();
        $this->assertSame($this->ama->id, $paiement->eleve_id);
        $this->assertEquals(300, $paiement->montant);
        $this->assertSame('2026-10-05', $paiement->date->toDateString());

        $liste = $this->get('/cantine')->assertOk();
        $this->assertSame(['Kofi'], $liste->viewData('nonPayes')->pluck('prenom')->all());
        $this->assertSame(1, $liste->viewData('payes')->count());

        // Cocher deux fois ne crée pas de doublon
        $this->post("/cantine/eleves/{$this->ama->id}/payer");
        $this->assertSame(1, PaiementCantine::count());

        // Le lendemain, la liste repart complète
        $this->travel(1)->days();
        $this->assertSame(['Ama', 'Kofi'], $this->get('/cantine')->viewData('nonPayes')->pluck('prenom')->all());

        // On peut consulter la veille : Kofi n'avait pas payé
        $veille = $this->get('/cantine?date=2026-10-05');
        $this->assertSame(['Kofi'], $veille->viewData('nonPayes')->pluck('prenom')->all());

        // Un paiement en retard pour la veille reste possible
        $this->post("/cantine/eleves/{$this->kofi->id}/payer?date=2026-10-05")
            ->assertRedirect('/cantine?date=2026-10-05');
        $this->assertTrue($this->get('/cantine?date=2026-10-05')->viewData('nonPayes')->isEmpty());
    }

    public function test_annuler_une_erreur_remet_l_eleve_dans_la_liste(): void
    {
        $this->inscrire(300, [$this->ama]);

        $this->actingAs($this->admin)->post("/cantine/eleves/{$this->ama->id}/payer");
        $paiement = PaiementCantine::sole();

        $this->delete("/cantine/paiements/{$paiement->id}")->assertRedirect('/cantine');

        $this->assertSame(0, PaiementCantine::count());
        $this->assertSame(['Ama'], $this->get('/cantine')->viewData('nonPayes')->pluck('prenom')->all());
    }

    public function test_impossible_de_cocher_sans_prix_defini(): void
    {
        $this->ama->update(['inscrit_cantine' => true]);

        $this->actingAs($this->admin)->get('/cantine')->assertOk()->assertSee('pas encore défini');

        $this->post("/cantine/eleves/{$this->ama->id}/payer")->assertSessionHas('error');
        $this->assertSame(0, PaiementCantine::count());
    }

    public function test_changer_le_prix_ne_modifie_pas_les_paiements_passes(): void
    {
        $this->inscrire(300, [$this->ama, $this->kofi]);
        $this->actingAs($this->admin)->post("/cantine/eleves/{$this->ama->id}/payer");

        $this->inscrire(350, [$this->ama, $this->kofi]);
        $this->post("/cantine/eleves/{$this->kofi->id}/payer");

        $this->assertEquals([300, 350], PaiementCantine::orderBy('id')->pluck('montant')->map(fn ($m) => (float) $m)->all());
        $this->assertSame(650, (int) $this->get('/cantine')->viewData('payes')->sum('montant'));
    }

    public function test_une_ecole_ne_peut_pas_cocher_les_eleves_d_une_autre(): void
    {
        $this->inscrire(300, [$this->ama]);
        $adminB = User::factory()->admin()->create();
        $adminB->ecole->update(['prix_cantine' => 200]);

        $this->actingAs($adminB)->post("/cantine/eleves/{$this->ama->id}/payer")->assertNotFound();
        $this->get('/cantine')->assertOk()->assertDontSee('Ama');

        $this->put('/cantine/parametres', ['prix_cantine' => 200, 'eleves' => [$this->ama->id]])
            ->assertSessionHasErrors('eleves.0');

        $this->assertSame(0, PaiementCantine::count());
    }

    public function test_seul_l_administration_gere_la_cantine(): void
    {
        $enseignant = User::factory()->enseignant()->create(['ecole_id' => $this->admin->ecole_id]);

        $this->actingAs($enseignant)->get('/cantine')->assertForbidden();
        $this->post("/cantine/eleves/{$this->ama->id}/payer")->assertForbidden();
    }

    private function inscrire(int $prix, array $eleves): void
    {
        $this->actingAs($this->admin)->put('/cantine/parametres', [
            'prix_cantine' => $prix,
            'eleves' => array_map(fn (Eleve $eleve) => $eleve->id, $eleves),
        ])->assertSessionHasNoErrors();
    }

    private function makeClasse(int $ecoleId): Classe
    {
        $annee = AnneeScolaire::create([
            'ecole_id' => $ecoleId,
            'libelle' => '2026-2027',
            'date_debut' => '2026-09-15',
            'date_fin' => '2027-07-05',
            'active' => true,
        ]);

        return Classe::create(['ecole_id' => $ecoleId, 'nom' => 'CM2 A', 'annee_scolaire_id' => $annee->id]);
    }

    private function makeEleve(Classe $classe, string $matricule, string $prenom): Eleve
    {
        return Eleve::create([
            'ecole_id' => $classe->ecole_id,
            'classe_id' => $classe->id,
            'matricule' => $matricule,
            'nom' => 'Mensah',
            'prenom' => $prenom,
            'sexe' => 'F',
            'date_naissance' => '2015-01-01',
            'statut' => Eleve::STATUT_ACTIF,
            'date_inscription' => '2026-09-15',
        ]);
    }
}
