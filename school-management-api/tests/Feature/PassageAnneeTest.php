<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Trimestre;
use App\Models\User;
use App\Support\NiveauxScolaires;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassageAnneeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private AnneeScolaire $annee;

    private Classe $sixieme;

    private Classe $terminale;

    private Eleve $ama;

    private Eleve $kofi;

    private Eleve $sena;

    private Eleve $yao;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $ecoleId = $this->admin->ecole_id;

        $this->annee = AnneeScolaire::create([
            'ecole_id' => $ecoleId, 'libelle' => '2026-2027',
            'date_debut' => '2026-09-14', 'date_fin' => '2027-07-16', 'active' => true,
        ]);
        $this->sixieme = Classe::create(['ecole_id' => $ecoleId, 'nom' => '6ème A', 'niveau' => '6ème', 'annee_scolaire_id' => $this->annee->id]);
        $this->terminale = Classe::create(['ecole_id' => $ecoleId, 'nom' => 'Tle D', 'niveau' => 'Tle', 'annee_scolaire_id' => $this->annee->id]);

        $this->ama = $this->makeEleve($this->sixieme, 'P-001', 'Ama');
        $this->kofi = $this->makeEleve($this->sixieme, 'P-002', 'Kofi');
        $this->sena = $this->makeEleve($this->sixieme, 'P-003', 'Sena');
        $this->yao = $this->makeEleve($this->terminale, 'P-004', 'Yao');

        // Bulletins de l'année : Kofi est sous la moyenne
        $trimestre = Trimestre::create(['ecole_id' => $ecoleId, 'annee_scolaire_id' => $this->annee->id, 'nom' => '1er trimestre', 'ordre' => 1, 'date_debut' => '2026-09-14', 'date_fin' => '2026-12-23']);
        foreach ([[$this->ama, 14.5], [$this->kofi, 8.25], [$this->sena, 11]] as [$eleve, $moyenne]) {
            Bulletin::create(['ecole_id' => $ecoleId, 'eleve_id' => $eleve->id, 'trimestre_id' => $trimestre->id, 'moyenne_generale' => $moyenne, 'genere_le' => now()]);
        }

        $matiere = Matiere::create(['ecole_id' => $ecoleId, 'nom' => 'Mathématiques', 'code' => 'MATH', 'coefficient_defaut' => 4]);
        $enseignant = Enseignant::create(['ecole_id' => $ecoleId, 'nom' => 'Koffi', 'prenom' => 'Ama']);
        Note::create([
            'ecole_id' => $ecoleId, 'eleve_id' => $this->ama->id, 'matiere_id' => $matiere->id, 'classe_id' => $this->sixieme->id, 'enseignant_id' => $enseignant->id,
            'trimestre_id' => $trimestre->id, 'type' => 'devoir', 'valeur' => 15, 'bareme' => 20, 'coefficient' => 1, 'date_evaluation' => '2026-10-01',
        ]);
    }

    public function test_la_page_propose_la_classe_suivante_et_le_redoublement_sous_10(): void
    {
        $page = $this->actingAs($this->admin)->get('/passage-annee')->assertOk();

        $page->assertSee('value="2027-2028"', false)
            ->assertSee('value="5ème A"', false)
            ->assertSee('Fin de cycle');
        $this->assertEquals(8.25, $page->viewData('moyennes')[$this->kofi->id]);
        $this->assertMatchesRegularExpression('/name="decisions\['.$this->kofi->id.'\]".*?<option value="redouble" selected/s', $page->getContent());
    }

    public function test_le_passage_place_chaque_eleve_et_conserve_l_annee_passee(): void
    {
        $this->actingAs($this->admin)->post('/passage-annee', [
            'annee' => ['libelle' => '2027-2028', 'date_debut' => '2027-09-13', 'date_fin' => '2028-07-14'],
            'activer' => '1',
            'classes' => [
                $this->sixieme->id => ['destination' => '5ème A', 'fin_cycle' => '0'],
                $this->terminale->id => ['destination' => '', 'fin_cycle' => '1'],
            ],
            'decisions' => [
                $this->ama->id => 'passe',
                $this->kofi->id => 'redouble',
                $this->sena->id => 'quitte',
                $this->yao->id => 'passe',
            ],
        ])->assertRedirect('/annees-scolaires')->assertSessionHas('success');

        $nouvelle = AnneeScolaire::where('libelle', '2027-2028')->sole();
        $this->assertTrue($nouvelle->active);
        $this->assertFalse($this->annee->fresh()->active);

        $cinquieme = Classe::where('annee_scolaire_id', $nouvelle->id)->where('nom', '5ème A')->sole();
        $sixiemeRedoublants = Classe::where('annee_scolaire_id', $nouvelle->id)->where('nom', '6ème A')->sole();
        $this->assertSame('5ème', $cinquieme->niveau);

        $this->assertSame($cinquieme->id, $this->ama->fresh()->classe_id);
        $this->assertSame($sixiemeRedoublants->id, $this->kofi->fresh()->classe_id);
        $this->assertSame(Eleve::STATUT_INACTIF, $this->sena->fresh()->statut);
        $this->assertSame(Eleve::STATUT_DIPLOME, $this->yao->fresh()->statut);

        // L'année passée reste intacte : ses classes, ses notes, ses bulletins
        $this->assertTrue(Classe::whereKey([$this->sixieme->id, $this->terminale->id])->count() === 2);
        $this->assertSame($this->sixieme->id, Note::sole()->classe_id);
        $this->assertSame(3, Bulletin::count());
    }

    public function test_sans_reglage_les_suggestions_sont_appliquees(): void
    {
        $this->actingAs($this->admin)->post('/passage-annee', [
            'annee' => ['libelle' => '2027-2028', 'date_debut' => '2027-09-13', 'date_fin' => '2028-07-14'],
        ])->assertSessionHasNoErrors();

        $nouvelle = AnneeScolaire::where('libelle', '2027-2028')->sole();
        $this->assertSame('5ème A', $this->ama->fresh()->classe->nom);
        $this->assertSame($nouvelle->id, $this->ama->fresh()->classe->annee_scolaire_id);
        $this->assertSame(Eleve::STATUT_DIPLOME, $this->yao->fresh()->statut);
        $this->assertTrue($this->annee->fresh()->active, 'Sans case cochée, l\'année en cours reste active.');
    }

    public function test_la_nouvelle_annee_doit_etre_differente_et_valide(): void
    {
        $this->actingAs($this->admin)->post('/passage-annee', [
            'annee' => ['libelle' => '2026-2027', 'date_debut' => '2027-09-13', 'date_fin' => '2027-01-01'],
        ])->assertSessionHasErrors(['annee.libelle', 'annee.date_fin']);

        $this->assertSame($this->sixieme->id, $this->ama->fresh()->classe_id);
    }

    public function test_reserve_a_l_administration_de_l_ecole(): void
    {
        $enseignant = User::factory()->enseignant()->create(['ecole_id' => $this->admin->ecole_id]);
        $this->actingAs($enseignant)->get('/passage-annee')->assertForbidden();

        $adminB = User::factory()->admin()->create();
        AnneeScolaire::create(['ecole_id' => $adminB->ecole_id, 'libelle' => '2026-2027', 'date_debut' => '2026-09-14', 'date_fin' => '2027-07-16', 'active' => true]);
        $this->actingAs($adminB)->post('/passage-annee', [
            'annee' => ['libelle' => '2027-2028', 'date_debut' => '2027-09-13', 'date_fin' => '2028-07-14'],
            'decisions' => [$this->ama->id => 'quitte'],
        ]);

        $this->assertSame(Eleve::STATUT_ACTIF, $this->ama->fresh()->statut);
        $this->assertSame($this->sixieme->id, $this->ama->fresh()->classe_id);
    }

    public function test_les_niveaux_togolais_sont_reconnus(): void
    {
        $this->assertSame('5ème A', NiveauxScolaires::classeSuivante('6ème A'));
        $this->assertSame('6ème B', NiveauxScolaires::classeSuivante('CM2 B'));
        $this->assertSame('2nde A4', NiveauxScolaires::classeSuivante('3e A4'));
        $this->assertSame('Tle D', NiveauxScolaires::classeSuivante('1ère D'));
        $this->assertSame('CP1', NiveauxScolaires::classeSuivante('Grande section'));
        $this->assertNull(NiveauxScolaires::classeSuivante('Terminale C'));
        $this->assertSame('Classe des grands', NiveauxScolaires::classeSuivante('Classe des grands'));
    }

    private function makeEleve(Classe $classe, string $matricule, string $prenom): Eleve
    {
        return Eleve::create([
            'ecole_id' => $classe->ecole_id, 'classe_id' => $classe->id, 'matricule' => $matricule,
            'nom' => 'Agbeko', 'prenom' => $prenom, 'sexe' => 'F', 'date_naissance' => '2014-01-01',
            'statut' => Eleve::STATUT_ACTIF, 'date_inscription' => '2026-09-14',
        ]);
    }
}
