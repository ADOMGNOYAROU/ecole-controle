<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifierDeploiementTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_verification_echoue_hors_production(): void
    {
        $this->artisan('app:verifier-deploiement --sans-reseau')
            ->expectsOutputToContain('APP_ENV=testing (attendu : production)')
            ->assertFailed();
    }

    public function test_la_verification_passe_avec_une_configuration_de_production(): void
    {
        User::factory()->superAdmin()->create();
        $this->app['env'] = 'production';
        config([
            'app.debug' => false,
            'app.url' => 'https://ecole.example.tg',
            'services.paydunya.mode' => 'live',
            'services.paydunya.master_key' => 'master',
            'services.paydunya.public_key' => 'live_public_x',
            'services.paydunya.private_key' => 'live_private_x',
            'services.paydunya.token' => 'token',
        ]);

        $this->artisan('app:verifier-deploiement --sans-reseau')
            ->expectsOutputToContain('Configuration prête pour la production.')
            ->assertSuccessful();
    }
}
