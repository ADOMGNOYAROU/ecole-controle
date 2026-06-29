<?php

namespace Tests\Feature;

use App\Models\Ecole;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_peut_lister_toutes_les_ecoles(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->admin()->create();
        User::factory()->admin()->create();

        $response = $this->actingAs($superAdmin)->get('/super-admin/ecoles');

        $response->assertOk();
        $this->assertSame(2, Ecole::count());
    }

    public function test_admin_decole_ne_peut_pas_acceder_au_back_office(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/super-admin/ecoles')->assertForbidden();
    }

    public function test_super_admin_peut_suspendre_et_reactiver_une_ecole(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $ecole = $admin->ecole;

        $this->actingAs($superAdmin)->patch("/super-admin/ecoles/{$ecole->id}/suspendre");
        $this->assertSame(Ecole::STATUT_SUSPENDU, $ecole->refresh()->statut);

        $this->actingAs($admin)->get('/dashboard')->assertRedirect('/abonnement');

        $this->actingAs($superAdmin)->patch("/super-admin/ecoles/{$ecole->id}/activer");
        $this->assertSame(Ecole::STATUT_ACTIF, $ecole->refresh()->statut);
    }

    public function test_super_admin_peut_confirmer_manuellement_une_facture(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create(['ecole_id' => Ecole::factory()->gratuit()]);
        $ecole = $admin->ecole;

        $facture = Facture::create([
            'ecole_id' => $ecole->id,
            'montant' => Ecole::TARIF_PREMIUM_TRIMESTRIEL,
            'date_echeance' => now()->addDays(7),
            'statut' => Facture::STATUT_EN_ATTENTE,
        ]);

        $this->assertFalse($ecole->aAccesPremium());

        $response = $this->actingAs($superAdmin)->post("/super-admin/factures/{$facture->id}/confirmer", [
            'methode_paiement' => 'flooz',
            'reference_transaction' => 'FLZ-12345',
        ]);

        $response->assertRedirect();
        $facture->refresh();
        $this->assertSame(Facture::STATUT_PAYEE, $facture->statut);
        $this->assertSame('flooz', $facture->methode_paiement);
        $this->assertTrue($ecole->refresh()->aAccesPremium());
    }
}
