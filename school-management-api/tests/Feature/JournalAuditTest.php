<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\JournalAudit;
use App\Models\Note;
use App\Models\Presence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class JournalAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => 'Directrice Akpene']);
        $this->superAdmin = User::factory()->superAdmin()->create(['name' => 'Adom']);
    }

    public function test_creation_modification_et_suppression_sont_journalisees_avec_avant_apres(): void
    {
        $this->actingAs($this->admin);
        $eleve = $this->makeEleve();

        $eleve->update(['prenom' => 'Kossi']);
        $eleve->delete();

        $entrees = JournalAudit::where('objet_type', 'Eleve')->where('objet_id', $eleve->id)->orderBy('id')->get();

        $this->assertSame(['creation', 'modification', 'suppression'], $entrees->pluck('action')->all());

        $modification = $entrees[1];
        $this->assertSame(['avant' => 'Ama', 'apres' => 'Kossi'], $modification->changements['prenom']);
        $this->assertArrayNotHasKey('updated_at', $modification->changements);
        $this->assertSame('Directrice Akpene', $modification->user_nom);
        $this->assertSame($this->admin->id, $modification->user_id);
        $this->assertSame($this->admin->ecole_id, $modification->ecole_id);
    }

    public function test_le_mot_de_passe_est_masque(): void
    {
        $this->actingAs($this->admin);
        $this->admin->update(['password' => 'nouveau-secret-123']);

        $entree = JournalAudit::where('objet_type', 'User')->where('action', 'modification')->sole();

        $this->assertSame('••••', $entree->changements['password']['apres']);
        $this->assertStringNotContainsString('nouveau-secret', json_encode($entree->changements));
    }

    public function test_la_saisie_de_notes_et_presences_ne_remplit_pas_le_journal(): void
    {
        $this->assertContains('creation', Note::AUDIT_IGNORE);
        $this->assertContains('creation', Presence::AUDIT_IGNORE);
    }

    public function test_connexions_reussie_echouee_et_deconnexion_sont_journalisees(): void
    {
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'mauvais'])->assertSessionHasErrors();
        $echec = JournalAudit::where('action', 'connexion_echouee')->sole();
        $this->assertSame($this->admin->email, $echec->changements['email_saisi']);
        $this->assertSame($this->admin->ecole_id, $echec->ecole_id);
        $this->assertNull($echec->user_id);

        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password'])->assertRedirect();
        $this->assertSame($this->admin->id, JournalAudit::where('action', 'connexion')->sole()->user_id);

        $this->post('/logout');
        $this->assertSame($this->admin->id, JournalAudit::where('action', 'deconnexion')->sole()->user_id);
    }

    public function test_les_entrees_sont_infalsifiables(): void
    {
        $this->actingAs($this->admin);
        $this->makeEleve();
        $entree = JournalAudit::where('objet_type', 'Eleve')->firstOrFail();

        try {
            $entree->update(['user_nom' => 'Quelqu’un d’autre']);
            $this->fail('La modification aurait dû être refusée.');
        } catch (LogicException) {
        }

        try {
            $entree->delete();
            $this->fail('La suppression aurait dû être refusée.');
        } catch (LogicException) {
        }

        $this->assertSame('Directrice Akpene', $entree->fresh()->user_nom);
    }

    public function test_seul_le_super_admin_voit_le_journal(): void
    {
        $this->actingAs($this->admin)->get('/super-admin/journal')->assertForbidden();
        $this->actingAs($this->admin)->get('/super-admin/journal/export')->assertForbidden();

        $this->actingAs($this->superAdmin)->get('/super-admin/journal')->assertOk()->assertSee("Journal d'audit", false);
    }

    public function test_filtres_du_journal(): void
    {
        $this->actingAs($this->admin);
        $this->makeEleve();

        $autreAdmin = User::factory()->admin()->create(['name' => 'Directeur Yao']);
        $this->actingAs($autreAdmin);
        AnneeScolaire::create([
            'ecole_id' => $autreAdmin->ecole_id,
            'libelle' => 'Annee Ecole Yao',
            'date_debut' => '2026-09-15',
            'date_fin' => '2027-07-05',
        ]);

        $this->actingAs($this->superAdmin)
            ->get('/super-admin/journal?ecole_id='.$this->admin->ecole_id)
            ->assertOk()
            ->assertSee('Ama Mensah')
            ->assertDontSee('Annee Ecole Yao');

        $this->get('/super-admin/journal?utilisateur=Yao&objet_type=AnneeScolaire')
            ->assertOk()
            ->assertSee('Annee Ecole Yao')
            ->assertDontSee('Ama Mensah');
    }

    public function test_export_csv_est_lui_meme_journalise(): void
    {
        $this->actingAs($this->admin);
        $this->makeEleve();

        $reponse = $this->actingAs($this->superAdmin)->get('/super-admin/journal/export?action=creation');

        $reponse->assertOk();
        $this->assertStringContainsString('text/csv', $reponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Ama Mensah', $reponse->streamedContent());

        $export = JournalAudit::where('action', 'export_journal')->sole();
        $this->assertSame($this->superAdmin->id, $export->user_id);
        $this->assertSame(['action' => 'creation'], $export->changements);
    }

    public function test_la_fiche_ecole_montre_l_activite_et_trace_la_consultation(): void
    {
        $this->actingAs($this->admin);
        $this->makeEleve();

        $this->actingAs($this->superAdmin)
            ->get('/super-admin/ecoles/'.$this->admin->ecole_id)
            ->assertOk()
            ->assertSee('Activité récente')
            ->assertSee('Ama Mensah');

        $consultation = JournalAudit::where('action', 'consultation')->sole();
        $this->assertSame($this->superAdmin->id, $consultation->user_id);
        $this->assertSame($this->admin->ecole_id, $consultation->ecole_id);
    }

    public function test_la_purge_supprime_seulement_les_entrees_de_plus_de_12_mois(): void
    {
        $this->actingAs($this->admin);
        $this->makeEleve();
        $ancienne = JournalAudit::firstOrFail();
        DB::table('journal_audit')->where('id', $ancienne->id)->update(['created_at' => now()->subMonths(13)]);
        $total = JournalAudit::count();

        $this->artisan('journal:purger')->assertSuccessful();

        $this->assertSame($total - 1, JournalAudit::count());
        $this->assertNull(JournalAudit::find($ancienne->id));
    }

    private function makeEleve(): Eleve
    {
        $ecoleId = $this->admin->ecole_id;
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
            'matricule' => 'J-001',
            'nom' => 'Mensah',
            'prenom' => 'Ama',
            'sexe' => 'F',
            'date_naissance' => '2015-01-01',
            'statut' => Eleve::STATUT_ACTIF,
            'date_inscription' => '2026-09-15',
        ]);
    }
}
