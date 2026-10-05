<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Eleve;
use App\Models\Facture;
use App\Models\JournalAudit;
use App\Models\NoteInterne;
use App\Models\Paiement;
use App\Models\User;
use App\Services\SuiviEcoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminSuiviTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 10, 5)->setTime(10, 0));
        $this->superAdmin = User::factory()->superAdmin()->create();
    }

    public function test_tour_de_controle(): void
    {
        Ecole::factory()->create(['nom' => 'École Les Palmiers', 'statut' => Ecole::STATUT_ESSAI, 'trial_ends_at' => now()->addDays(3)]);

        $dormante = Ecole::factory()->create(['nom' => 'École Dormante', 'statut' => Ecole::STATUT_ESSAI, 'trial_ends_at' => now()->addDays(20), 'created_at' => now()->subDays(60)]);
        User::factory()->admin()->create(['ecole_id' => $dormante->id, 'derniere_connexion_le' => now()->subDays(20)]);

        $active = Ecole::factory()->create(['nom' => 'École Active', 'created_at' => now()->subDays(60)]);
        User::factory()->admin()->create(['ecole_id' => $active->id, 'derniere_connexion_le' => now()->subDay()]);
        // La fabrique crée l'abonnement actif de l'école active
        Facture::create(['ecole_id' => $active->id, 'montant' => 25000, 'date_echeance' => now(), 'statut' => Facture::STATUT_PAYEE, 'methode_paiement' => 'paydunya', 'reference_transaction' => 'tok', 'payee_le' => now()]);

        $page = $this->actingAs($this->superAdmin)->get('/super-admin')->assertOk();

        // 1 abonnement de 25 000 F pour 2 mois = 12 500 F par mois
        $page->assertSee('12 500')
            ->assertSee('École Les Palmiers')
            ->assertSee('École Dormante')
            ->assertSee('33 %')
            ->assertSee('Revenus encaissés sur 12 mois');

        $endormies = app(SuiviEcoles::class)->ecolesEndormies(app(SuiviEcoles::class)->sante());
        $this->assertSame(['École Dormante'], $endormies->pluck('nom')->all());
        $this->assertSame(25000.0, app(SuiviEcoles::class)->revenus12Mois()['2026-10']);
        $this->assertSame(1, app(SuiviEcoles::class)->payDunyaDuMois()['reussis']);
    }

    public function test_note_de_sante(): void
    {
        $admin = User::factory()->admin()->create(['derniere_connexion_le' => now()]);
        $eleve = $this->makeEleve($admin->ecole_id);

        $sante = app(SuiviEcoles::class)->sante()[$admin->ecole_id];
        $this->assertSame(30, $sante['score']);
        $this->assertSame('faible', $sante['niveau']);

        Paiement::create([
            'ecole_id' => $admin->ecole_id, 'eleve_id' => $eleve->id, 'annee_scolaire_id' => $eleve->classe->annee_scolaire_id,
            'type' => 'scolarite', 'montant' => 10000, 'montant_paye' => 10000, 'date_echeance' => now(), 'date_paiement' => now(), 'statut' => 'paye',
        ]);

        $sante = app(SuiviEcoles::class)->sante()[$admin->ecole_id];
        $this->assertSame(50, $sante['score']);
        $this->assertSame('moyenne', $sante['niveau']);
    }

    public function test_la_derniere_connexion_est_enregistree_sans_remplir_le_journal(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect();

        $this->assertTrue($admin->fresh()->derniere_connexion_le->equalTo(now()));
        $this->assertFalse(JournalAudit::where('objet_type', 'User')->where('action', 'modification')->exists());
    }

    public function test_prolonger_l_essai(): void
    {
        $ecole = Ecole::factory()->create(['statut' => Ecole::STATUT_EXPIRE, 'plan' => Ecole::PLAN_GRATUIT, 'trial_ends_at' => now()->subDays(5)]);

        $this->actingAs($this->superAdmin)
            ->post("/super-admin/ecoles/{$ecole->id}/prolonger-essai", ['jours' => 15])
            ->assertSessionHas('success');

        $ecole->refresh();
        $this->assertSame('2026-10-20', $ecole->trial_ends_at->toDateString());
        $this->assertSame(Ecole::STATUT_ESSAI, $ecole->statut);
        $this->assertSame(Ecole::PLAN_PREMIUM, $ecole->plan);
        $this->assertSame(15, JournalAudit::where('action', 'prolongation_essai')->sole()->changements['jours']);

        // Une école déjà payante n'a pas d'essai à prolonger
        $payante = Ecole::factory()->create(['statut' => Ecole::STATUT_ACTIF]);
        $this->post("/super-admin/ecoles/{$payante->id}/prolonger-essai", ['jours' => 30])->assertSessionHas('error');
        $this->assertNull($payante->fresh()->trial_ends_at);
    }

    public function test_reinitialiser_l_acces_d_un_utilisateur(): void
    {
        $directeur = User::factory()->admin()->create(['phone' => '90112233']);

        $reponse = $this->actingAs($this->superAdmin)
            ->post("/super-admin/ecoles/{$directeur->ecole_id}/utilisateurs/{$directeur->id}/reinitialiser");

        $acces = $reponse->assertSessionHas('acces_temporaire')->baseResponse->getSession()->get('acces_temporaire');
        $directeur->refresh();
        $this->assertTrue(Hash::check($acces['mot_de_passe'], $directeur->password));
        $this->assertTrue($directeur->must_change_password);
        $this->assertStringStartsWith('https://wa.me/22890112233', $acces['whatsapp']);

        $entree = JournalAudit::where('action', 'reinitialisation_acces')->sole();
        $this->assertSame($this->superAdmin->id, $entree->user_id);
        $this->assertStringNotContainsString($acces['mot_de_passe'], json_encode(JournalAudit::all()->toArray()));

        // Un utilisateur d'une autre école ne peut pas être visé par cette fiche
        $autre = User::factory()->admin()->create();
        $this->post("/super-admin/ecoles/{$directeur->ecole_id}/utilisateurs/{$autre->id}/reinitialiser")->assertNotFound();

        $this->actingAs($directeur)
            ->post("/super-admin/ecoles/{$directeur->ecole_id}/utilisateurs/{$directeur->id}/reinitialiser")
            ->assertForbidden();
    }

    public function test_notes_internes(): void
    {
        $directeur = User::factory()->admin()->create();
        $ecoleId = $directeur->ecole_id;

        $this->actingAs($this->superAdmin)
            ->post("/super-admin/ecoles/{$ecoleId}/notes", ['contenu' => 'Rappeler lundi pour le Premium'])
            ->assertSessionHas('success');

        $this->get("/super-admin/ecoles/{$ecoleId}")->assertOk()->assertSee('Rappeler lundi pour le Premium');

        $note = NoteInterne::sole();
        $this->delete("/super-admin/ecoles/{$ecoleId}/notes/{$note->id}")->assertSessionHas('success');
        $this->assertSame(0, NoteInterne::count());

        $this->actingAs($directeur)->post("/super-admin/ecoles/{$ecoleId}/notes", ['contenu' => 'x'])->assertForbidden();
    }

    public function test_la_fiche_ecole_est_complete(): void
    {
        $directeur = User::factory()->admin()->create(['name' => 'Directrice Akpene', 'derniere_connexion_le' => now()->subHour()]);

        $this->actingAs($this->superAdmin)
            ->get("/super-admin/ecoles/{$directeur->ecole_id}")
            ->assertOk()
            ->assertSee('Directrice Akpene')
            ->assertSee(now()->subHour()->format('d/m/Y H:i'))
            ->assertSee('Accéder en mode support')
            ->assertSee('Santé (30 jours)')
            // Suspendre / Activer passent bien en PATCH (un formulaire HTML ne sait faire que GET/POST)
            ->assertSee('name="_method" value="PATCH"', false);

        $this->get('/super-admin/ecoles')->assertOk()->assertSee('Santé');
    }

    private function makeEleve(int $ecoleId): Eleve
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
            'matricule' => 'S-001',
            'nom' => 'Mensah',
            'prenom' => 'Ama',
            'sexe' => 'F',
            'date_naissance' => '2015-01-01',
            'statut' => Eleve::STATUT_ACTIF,
            'date_inscription' => '2026-09-15',
        ]);
    }
}
