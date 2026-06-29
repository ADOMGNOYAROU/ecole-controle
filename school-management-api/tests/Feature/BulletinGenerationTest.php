<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Trimestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulletinGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_bulletin_pdf_for_eleve(): void
    {
        $admin = User::factory()->admin()->create();
        $ecoleId = $admin->ecole_id;

        $annee = AnneeScolaire::create([
            'ecole_id' => $ecoleId,
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-15',
            'date_fin' => '2026-07-05',
            'active' => true,
        ]);

        $trimestre = Trimestre::create([
            'ecole_id' => $ecoleId,
            'annee_scolaire_id' => $annee->id,
            'nom' => '1er trimestre',
            'ordre' => 1,
            'date_debut' => '2025-09-15',
            'date_fin' => '2025-12-19',
        ]);

        $classe = Classe::create(['ecole_id' => $ecoleId, 'nom' => '6ème A', 'niveau' => '6ème', 'annee_scolaire_id' => $annee->id]);
        $matiere = Matiere::create(['ecole_id' => $ecoleId, 'nom' => 'Mathématiques', 'code' => 'MATH', 'coefficient_defaut' => 4]);
        $enseignant = Enseignant::create(['ecole_id' => $ecoleId, 'nom' => 'Dupont', 'prenom' => 'Marc']);

        $eleve = Eleve::create([
            'ecole_id' => $ecoleId,
            'matricule' => 'ELV0010',
            'nom' => 'Petit',
            'prenom' => 'Léa',
            'sexe' => 'F',
            'date_naissance' => '2012-03-01',
            'classe_id' => $classe->id,
            'statut' => 'actif',
            'date_inscription' => '2025-09-15',
        ]);

        Note::create([
            'ecole_id' => $ecoleId,
            'eleve_id' => $eleve->id,
            'matiere_id' => $matiere->id,
            'enseignant_id' => $enseignant->id,
            'classe_id' => $classe->id,
            'trimestre_id' => $trimestre->id,
            'type' => 'devoir',
            'valeur' => 15,
            'bareme' => 20,
            'coefficient' => 1,
            'date_evaluation' => '2025-10-01',
        ]);

        $response = $this->actingAs($admin)->get("/bulletins/eleves/{$eleve->id}/trimestres/{$trimestre->id}");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertDatabaseHas('bulletins', ['eleve_id' => $eleve->id, 'trimestre_id' => $trimestre->id]);
    }

    public function test_prof_titulaire_peut_generer_les_bulletins_de_sa_classe(): void
    {
        $admin = User::factory()->admin()->create();
        $ecoleId = $admin->ecole_id;

        $annee = AnneeScolaire::create([
            'ecole_id' => $ecoleId,
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-15',
            'date_fin' => '2026-07-05',
            'active' => true,
        ]);

        $trimestre = Trimestre::create([
            'ecole_id' => $ecoleId,
            'annee_scolaire_id' => $annee->id,
            'nom' => '1er trimestre',
            'ordre' => 1,
            'date_debut' => '2025-09-15',
            'date_fin' => '2025-12-19',
        ]);

        $userEnseignant = User::factory()->enseignant()->create(['ecole_id' => $ecoleId]);
        $enseignant = Enseignant::create(['ecole_id' => $ecoleId, 'user_id' => $userEnseignant->id, 'nom' => 'Dupont', 'prenom' => 'Marc']);

        $classe = Classe::create([
            'ecole_id' => $ecoleId,
            'nom' => '6ème A',
            'niveau' => '6ème',
            'annee_scolaire_id' => $annee->id,
            'enseignant_principal_id' => $enseignant->id,
        ]);

        $response = $this->actingAs($userEnseignant)->post("/bulletins/classes/{$classe->id}/trimestres/{$trimestre->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_enseignant_non_titulaire_ne_peut_pas_generer_de_bulletins(): void
    {
        $admin = User::factory()->admin()->create();
        $ecoleId = $admin->ecole_id;

        $annee = AnneeScolaire::create([
            'ecole_id' => $ecoleId,
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-15',
            'date_fin' => '2026-07-05',
            'active' => true,
        ]);

        $trimestre = Trimestre::create([
            'ecole_id' => $ecoleId,
            'annee_scolaire_id' => $annee->id,
            'nom' => '1er trimestre',
            'ordre' => 1,
            'date_debut' => '2025-09-15',
            'date_fin' => '2025-12-19',
        ]);

        $userEnseignant = User::factory()->enseignant()->create(['ecole_id' => $ecoleId]);
        Enseignant::create(['ecole_id' => $ecoleId, 'user_id' => $userEnseignant->id, 'nom' => 'Sans', 'prenom' => 'Titulariat']);

        $classe = Classe::create(['ecole_id' => $ecoleId, 'nom' => '6ème A', 'niveau' => '6ème', 'annee_scolaire_id' => $annee->id]);

        $this->actingAs($userEnseignant)
            ->post("/bulletins/classes/{$classe->id}/trimestres/{$trimestre->id}")
            ->assertForbidden();

        $this->actingAs($userEnseignant)
            ->get('/bulletins')
            ->assertForbidden();
    }
}
