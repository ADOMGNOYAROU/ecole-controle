<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Eleve;
use App\Models\JournalAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesSupportTest extends TestCase
{
    use RefreshDatabase;

    private const MOTIF = 'Appel du directeur, bulletins introuvables';

    private User $superAdmin;

    private User $directeurA;

    private Ecole $ecoleA;

    private Eleve $ama;

    private Eleve $kofi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 10, 5)->setTime(10, 0));

        $this->superAdmin = User::factory()->superAdmin()->create(['name' => 'Adom']);
        $this->directeurA = User::factory()->admin()->create(['name' => 'Directrice Akpene']);
        $this->ecoleA = $this->directeurA->ecole;
        $directeurB = User::factory()->admin()->create();

        $this->ama = $this->makeEleve($this->ecoleA->id, 'Amavi');
        $this->kofi = $this->makeEleve($directeurB->ecole_id, 'Kofivi');
    }

    public function test_le_motif_est_obligatoire(): void
    {
        $this->actingAs($this->superAdmin)
            ->post("/super-admin/ecoles/{$this->ecoleA->id}/support", ['motif' => ''])
            ->assertSessionHasErrors('motif');

        $this->assertNull(session('acces_support'));
        $this->assertFalse(JournalAudit::where('action', 'acces_support')->exists());
    }

    public function test_le_super_admin_voit_l_ecole_comme_le_directeur_en_lecture_seule(): void
    {
        $this->entrerEnSupport()->assertRedirect('/dashboard');

        $entree = JournalAudit::where('action', 'acces_support')->sole();
        $this->assertSame($this->superAdmin->id, $entree->user_id);
        $this->assertSame($this->ecoleA->id, $entree->ecole_id);
        $this->assertSame(self::MOTIF, $entree->changements['motif']);

        // Il voit les élèves de l'école A, jamais ceux d'une autre école
        $this->get('/eleves')->assertOk()
            ->assertSee('Mode support')
            ->assertSee('Amavi')
            ->assertDontSee('Kofivi');
        $this->get("/eleves/{$this->kofi->id}")->assertNotFound();

        // Aucune modification possible
        $this->from('/eleves')->delete("/eleves/{$this->ama->id}")
            ->assertRedirect('/eleves')
            ->assertSessionHas('error');
        $this->assertNotNull($this->ama->fresh());

        // Chaque page vue est notée au nom du super administrateur
        $pages = JournalAudit::where('action', 'page_support')->get();
        $this->assertSame(['/eleves', '/eleves/'.$this->kofi->id], $pages->pluck('objet_libelle')->all());
        $this->assertSame([$this->superAdmin->id], $pages->pluck('user_id')->unique()->values()->all());
        $this->assertSame([$this->ecoleA->id], $pages->pluck('ecole_id')->unique()->values()->all());
    }

    public function test_les_messages_prives_restent_fermes(): void
    {
        $this->entrerEnSupport();

        $this->get('/messagerie')->assertRedirect('/dashboard')->assertSessionHas('error');
    }

    public function test_quitter_le_mode_support(): void
    {
        $this->entrerEnSupport();
        $this->get('/eleves');

        $this->post('/support/quitter')->assertRedirect("/super-admin/ecoles/{$this->ecoleA->id}");

        $this->assertNull(session('acces_support'));
        $fin = JournalAudit::where('action', 'fin_support')->sole();
        $this->assertSame('sortie_manuelle', $fin->changements['raison']);
        $this->assertSame(1, $fin->changements['pages_vues']);
        $this->assertSame($this->superAdmin->id, $fin->user_id);

        $this->get('/super-admin')->assertOk();
    }

    public function test_le_mode_support_s_arrete_apres_30_minutes(): void
    {
        $this->entrerEnSupport();

        $this->travel(31)->minutes();

        $this->get('/eleves')->assertRedirect("/super-admin/ecoles/{$this->ecoleA->id}");
        $this->assertNull(session('acces_support'));
        $this->assertSame('duree_ecoulee', JournalAudit::where('action', 'fin_support')->sole()->changements['raison']);
    }

    public function test_une_session_support_forgee_ne_donne_rien_a_un_autre_compte(): void
    {
        $intrus = User::factory()->admin()->create();

        $this->actingAs($intrus)
            ->withSession(['acces_support' => [
                'ecole_id' => $this->ecoleA->id, 'ecole_nom' => $this->ecoleA->nom, 'directeur_id' => $this->directeurA->id,
                'super_admin_id' => $this->superAdmin->id, 'motif' => self::MOTIF, 'debut' => now()->getTimestamp(),
                'expire' => now()->addMinutes(30)->getTimestamp(), 'pages' => 0,
            ]])
            ->get('/eleves')
            ->assertOk()
            ->assertDontSee('Amavi');

        $this->assertNull(session('acces_support'));
    }

    public function test_un_admin_ne_peut_pas_lancer_le_mode_support(): void
    {
        $this->actingAs($this->directeurA)
            ->post("/super-admin/ecoles/{$this->ecoleA->id}/support", ['motif' => self::MOTIF])
            ->assertForbidden();
    }

    private function entrerEnSupport()
    {
        return $this->actingAs($this->superAdmin)
            ->post("/super-admin/ecoles/{$this->ecoleA->id}/support", ['motif' => self::MOTIF]);
    }

    private function makeEleve(int $ecoleId, string $prenom): Eleve
    {
        $annee = AnneeScolaire::create([
            'ecole_id' => $ecoleId,
            'libelle' => '2026-2027',
            'date_debut' => '2026-09-15',
            'date_fin' => '2027-07-05',
            'active' => true,
        ]);
        $classe = Classe::create(['ecole_id' => $ecoleId, 'nom' => 'CM2 A', 'annee_scolaire_id' => $annee->id]);

        return Eleve::create([
            'ecole_id' => $ecoleId,
            'classe_id' => $classe->id,
            'matricule' => 'S-'.$ecoleId,
            'nom' => 'Mensah',
            'prenom' => $prenom,
            'sexe' => 'F',
            'date_naissance' => '2015-01-01',
            'statut' => Eleve::STATUT_ACTIF,
            'date_inscription' => '2026-09-15',
        ]);
    }
}
