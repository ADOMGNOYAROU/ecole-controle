<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Trimestre;
use App\Models\User;
use App\Services\ProgressionService;
use Database\Seeders\ChargeDeTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

/**
 * Le nombre de requêtes d'une page ne doit pas grandir avec le nombre d'élèves
 * (problème trouvé par les tests de charge k6 de tests/charge).
 */
class PerformanceRequetesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Classe $classe;

    private Trimestre $trimestre;

    private Matiere $matiere;

    private Enseignant $enseignant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 10, 10)->setTime(10, 0));
        $this->admin = User::factory()->admin()->create();
        $ecoleId = $this->admin->ecole_id;

        $annee = AnneeScolaire::create(['ecole_id' => $ecoleId, 'libelle' => '2026-2027', 'date_debut' => '2026-09-14', 'date_fin' => '2027-07-03', 'active' => true]);
        $this->trimestre = Trimestre::create(['ecole_id' => $ecoleId, 'annee_scolaire_id' => $annee->id, 'nom' => '1er trimestre', 'ordre' => 1, 'date_debut' => '2026-09-14', 'date_fin' => '2026-12-18']);
        $this->classe = Classe::create(['ecole_id' => $ecoleId, 'nom' => 'CM2 A', 'annee_scolaire_id' => $annee->id]);
        $this->matiere = Matiere::create(['ecole_id' => $ecoleId, 'nom' => 'Mathématiques', 'code' => 'MATH', 'coefficient_defaut' => 4]);
        $this->enseignant = Enseignant::create(['ecole_id' => $ecoleId, 'nom' => 'Kodjo', 'prenom' => 'Komlan']);
    }

    public function test_les_eleves_a_risque_se_calculent_sans_une_requete_par_eleve(): void
    {
        $this->actingAs($this->admin);
        $this->eleves(3, 8.0);
        $avecTroisEleves = $this->compterRequetes(fn () => ProgressionService::idsElevesARisque());

        $this->eleves(15, 14.0);
        $ids = null;
        $avecDixHuitEleves = $this->compterRequetes(function () use (&$ids) {
            $ids = ProgressionService::idsElevesARisque();
        });

        $this->assertSame($avecTroisEleves, $avecDixHuitEleves);
        // Le résultat ne change pas : seuls les 3 élèves sous 10 de moyenne sont à risque
        $this->assertCount(3, $ids);
    }

    public function test_la_moyenne_est_la_meme_avec_ou_sans_notes_prechargees(): void
    {
        $this->actingAs($this->admin);
        $eleve = $this->eleves(1, 9.5)->first();

        $sansPrechargement = $eleve->fresh()->moyenneTrimestre($this->trimestre->id);
        $avecPrechargement = Eleve::with('notes')->find($eleve->id)->moyenneTrimestre($this->trimestre->id);

        $this->assertSame(9.5, $sansPrechargement);
        $this->assertSame($sansPrechargement, $avecPrechargement);
    }

    public function test_la_liste_des_paiements_ne_recharge_pas_l_ecole_a_chaque_ligne(): void
    {
        $this->actingAs($this->admin);
        $requetes = fn () => $this->compterRequetes(fn () => $this->get('/paiements')->assertOk());

        $this->eleves(2, 12.0);
        // Premier passage à vide : l'école de l'utilisateur connecté est chargée une fois puis gardée
        $requetes();
        $avecDeuxPaiements = $requetes();
        $this->eleves(10, 12.0);

        $this->assertSame($avecDeuxPaiements, $requetes());
    }

    public function test_le_generateur_de_donnees_de_charge(): void
    {
        $journalAvant = DB::table('journal_audit')->count();
        $_SERVER['CHARGE_ECOLES'] = '2';
        $_SERVER['CHARGE_ELEVES_PAR_CLASSE'] = '3';

        try {
            $this->seed(ChargeDeTestSeeder::class);
        } finally {
            unset($_SERVER['CHARGE_ECOLES'], $_SERVER['CHARGE_ELEVES_PAR_CLASSE']);
        }

        $this->assertSame(2, Ecole::where('slug', 'like', 'ecole-charge-%')->count());
        $this->assertSame(36, Eleve::withoutGlobalScopes()->where('matricule', 'like', 'CH%')->count());
        $this->assertTrue(User::where('email', 'directeur2@charge.test')->exists());
        $this->assertSame(20, User::where('email', 'like', 'parent%@charge.test')->count());
        // Aucune ligne de journal d'audit pour des données fictives
        $this->assertSame($journalAvant, DB::table('journal_audit')->count());

        $this->post('/login', ['email' => 'parent1-1@charge.test', 'password' => ChargeDeTestSeeder::MOT_DE_PASSE]);
        $this->get('/mes-enfants')->assertOk();
    }

    public function test_le_generateur_refuse_la_production(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(RuntimeException::class);
        (new ChargeDeTestSeeder)->run();
    }

    /** Crée des élèves avec une note de mathématiques et un paiement chacun. */
    private function eleves(int $nombre, float $note)
    {
        static $matricule = 0;

        return collect(range(1, $nombre))->map(function () use ($note, &$matricule) {
            $matricule++;
            $eleve = Eleve::create([
                'ecole_id' => $this->admin->ecole_id, 'classe_id' => $this->classe->id, 'matricule' => "P-{$matricule}",
                'nom' => 'Mensah', 'prenom' => "Élève {$matricule}", 'sexe' => 'F', 'date_naissance' => '2015-01-01',
                'statut' => Eleve::STATUT_ACTIF, 'date_inscription' => '2026-09-14',
            ]);
            Note::create([
                'ecole_id' => $this->admin->ecole_id, 'eleve_id' => $eleve->id, 'matiere_id' => $this->matiere->id,
                'enseignant_id' => $this->enseignant->id, 'classe_id' => $this->classe->id, 'trimestre_id' => $this->trimestre->id,
                'type' => 'devoir', 'valeur' => $note, 'bareme' => 20, 'coefficient' => 1, 'date_evaluation' => '2026-10-01',
            ]);
            DB::table('paiements')->insert([
                'ecole_id' => $this->admin->ecole_id, 'eleve_id' => $eleve->id, 'annee_scolaire_id' => $this->classe->annee_scolaire_id,
                'type' => 'scolarite', 'montant' => 150000, 'montant_paye' => 0, 'date_echeance' => '2026-11-15',
                'statut' => 'en_attente', 'created_at' => now(), 'updated_at' => now(),
            ]);
            // Un parent avec un téléphone : la ligne de paiement affiche alors le bouton WhatsApp
            $tuteurId = DB::table('tuteurs')->insertGetId([
                'ecole_id' => $this->admin->ecole_id, 'nom' => 'Mensah', 'prenom' => 'Parent', 'telephone' => '90112233',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('eleve_tuteur')->insert([
                'eleve_id' => $eleve->id, 'tuteur_id' => $tuteurId, 'lien_parente' => 'Parent', 'contact_principal' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            return $eleve;
        });
    }

    private function compterRequetes(callable $action): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $action();
        $nombre = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $nombre;
    }
}
