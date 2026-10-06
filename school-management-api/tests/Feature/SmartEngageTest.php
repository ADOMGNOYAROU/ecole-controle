<?php

namespace Tests\Feature;

use App\Models\Ecole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmartEngageTest extends TestCase
{
    use RefreshDatabase;

    private const INSCRIPTION = [
        'nom_ecole' => 'Collège Espoir',
        'ville' => 'Lomé',
        'admin_nom' => 'Directeur Espoir',
        'admin_email' => 'directeur@espoir.tg',
        'admin_password' => 'motdepasse123',
        'admin_password_confirmation' => 'motdepasse123',
    ];

    private function configurer(): void
    {
        config(['services.smart_engage.url' => 'https://smart-engage.test/', 'services.smart_engage.cle' => 'se_cle_ecole']);
    }

    public function test_le_directeur_inscrit_est_envoye_a_smart_engage(): void
    {
        $this->configurer();
        Http::fake(['smart-engage.test/*' => Http::response(['success' => true], 201)]);

        $this->post('/inscription', self::INSCRIPTION)->assertRedirect();

        Http::assertSent(fn (Request $requete) => $requete->url() === 'https://smart-engage.test/api/emails'
            && $requete->hasHeader('X-Site-Key', 'se_cle_ecole')
            && $requete['email'] === 'directeur@espoir.tg');
    }

    public function test_sans_configuration_rien_n_est_envoye(): void
    {
        Http::fake();

        $this->post('/inscription', self::INSCRIPTION)->assertRedirect();

        Http::assertNothingSent();
        $this->assertSame(1, Ecole::count());
    }

    public function test_une_panne_de_smart_engage_ne_bloque_pas_l_inscription(): void
    {
        $this->configurer();
        Http::fake(['smart-engage.test/*' => Http::response('erreur', 500)]);

        $this->post('/inscription', self::INSCRIPTION)->assertRedirect('/dashboard');

        $this->assertSame(1, Ecole::count());
        $this->assertAuthenticated();
    }

    public function test_synchroniser_les_directeurs_existants(): void
    {
        $this->artisan('smart-engage:synchroniser')->assertFailed();

        $this->configurer();
        Http::fake(['smart-engage.test/*' => Http::response(['success' => true], 200)]);
        User::factory()->admin()->count(2)->create();
        User::factory()->enseignant()->create();

        $this->artisan('smart-engage:synchroniser')
            ->expectsOutputToContain('2 contact(s) envoyé(s)')
            ->assertSuccessful();

        Http::assertSentCount(2);
    }
}
