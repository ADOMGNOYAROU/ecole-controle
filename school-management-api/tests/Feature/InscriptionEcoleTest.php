<?php

namespace Tests\Feature;

use App\Models\Ecole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InscriptionEcoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_ecole_peut_sinscrire_et_obtient_un_essai_premium(): void
    {
        $response = $this->post('/inscription', [
            'nom_ecole' => 'Collège Saint-Exupéry',
            'ville' => 'Lomé',
            'telephone' => '0600000000',
            'admin_nom' => 'Awa Mensah',
            'admin_email' => 'awa@college-test.tg',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');

        $ecole = Ecole::where('nom', 'Collège Saint-Exupéry')->first();
        $this->assertNotNull($ecole);
        $this->assertSame(Ecole::STATUT_ESSAI, $ecole->statut);
        $this->assertTrue($ecole->estEnEssai());
        $this->assertTrue($ecole->aAccesPremium());

        $admin = User::where('email', 'awa@college-test.tg')->first();
        $this->assertNotNull($admin);
        $this->assertSame($ecole->id, $admin->ecole_id);
        $this->assertTrue($admin->isAdmin());
        $this->assertAuthenticatedAs($admin);
    }

    public function test_inscription_requiert_un_email_unique(): void
    {
        User::factory()->admin()->create(['email' => 'deja@ecole.test']);

        $response = $this->post('/inscription', [
            'nom_ecole' => 'Autre École',
            'admin_nom' => 'Test',
            'admin_email' => 'deja@ecole.test',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('admin_email');
    }
}
