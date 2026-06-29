<?php

namespace Tests\Feature;

use App\Models\Eleve;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_account_management(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/comptes')->assertOk();
    }

    public function test_enseignant_cannot_access_account_management(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $this->actingAs($enseignant)->get('/comptes')->assertForbidden();
    }

    public function test_eleve_cannot_access_class_management(): void
    {
        $eleve = User::factory()->eleve()->create();

        $this->actingAs($eleve)->get('/classes')->assertForbidden();
    }

    public function test_eleve_can_access_own_space(): void
    {
        $user = User::factory()->eleve()->create();
        Eleve::create([
            'ecole_id' => $user->ecole_id,
            'user_id' => $user->id,
            'matricule' => 'ELV0099',
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'sexe' => 'F',
            'date_naissance' => '2012-01-01',
            'statut' => 'actif',
            'date_inscription' => '2025-09-15',
        ]);

        $this->actingAs($user)->get('/mon-espace/notes')->assertOk();
    }

    public function test_parent_cannot_access_eleve_space(): void
    {
        $parent = User::factory()->parent()->create();

        $this->actingAs($parent)->get('/mon-espace/notes')->assertForbidden();
    }

    public function test_free_plan_ecole_is_redirected_from_premium_routes(): void
    {
        $admin = User::factory()->admin()->for(\App\Models\Ecole::factory()->gratuit(), 'ecole')->create();

        $this->actingAs($admin)->get('/comptes')->assertRedirect('/abonnement');
    }

    public function test_premium_ecole_data_is_isolated_between_schools(): void
    {
        $adminA = User::factory()->admin()->create();
        $adminB = User::factory()->admin()->create();

        Eleve::create([
            'ecole_id' => $adminA->ecole_id,
            'matricule' => 'A-001',
            'nom' => 'Ecole A',
            'prenom' => 'Eleve',
            'sexe' => 'M',
            'date_naissance' => '2012-01-01',
            'statut' => 'actif',
            'date_inscription' => '2025-09-15',
        ]);

        $this->actingAs($adminB);
        $this->assertSame(0, Eleve::count());

        $this->actingAs($adminA);
        $this->assertSame(1, Eleve::count());
    }
}
