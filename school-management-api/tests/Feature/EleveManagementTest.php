<?php

namespace Tests\Feature;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EleveManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_eleve(): void
    {
        $admin = User::factory()->admin()->create();
        $classe = $this->makeClasse($admin->ecole_id);

        $response = $this->actingAs($admin)->post('/eleves', [
            'matricule' => 'ELV0001',
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'sexe' => 'F',
            'date_naissance' => '2012-05-10',
            'classe_id' => $classe->id,
            'statut' => 'actif',
            'date_inscription' => '2025-09-15',
        ]);

        $eleve = Eleve::where('matricule', 'ELV0001')->first();

        $response->assertRedirect("/eleves/{$eleve->id}");
        $this->assertDatabaseHas('eleves', ['matricule' => 'ELV0001', 'classe_id' => $classe->id]);
    }

    public function test_creating_eleve_requires_valid_data(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/eleves', ['matricule' => '']);

        $response->assertSessionHasErrors(['matricule', 'nom', 'prenom', 'sexe']);
    }

    public function test_enseignant_cannot_create_eleve(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $this->actingAs($enseignant)->post('/eleves', [
            'matricule' => 'ELV0002',
            'nom' => 'Doe',
            'prenom' => 'John',
            'sexe' => 'M',
            'date_naissance' => '2012-05-10',
            'statut' => 'actif',
            'date_inscription' => '2025-09-15',
        ])->assertForbidden();
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
