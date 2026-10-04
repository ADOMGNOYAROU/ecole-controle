<?php

namespace Tests\Feature\EndToEnd;

use App\Models\Ecole;
use App\Models\Facture;
use App\Models\User;
use App\Services\PayDunyaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Paydunya\Checkout\CheckoutInvoice;
use Tests\TestCase;

/**
 * Parcours d'abonnement Premium via PayDunya. Seuls les appels réseau vers PayDunya
 * (création / confirmation de facture, déboursement) sont simulés : contrôleurs,
 * service, activation de l'abonnement et webhook s'exécutent réellement.
 */
class AbonnementPayDunyaTest extends TestCase
{
    use RefreshDatabase;

    private const MASTER_KEY = 'cle-principale-de-test';

    private User $admin;

    private MockInterface $paydunya;

    private string $statutPayDunya = 'completed';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.paydunya.master_key' => self::MASTER_KEY]);
        User::factory()->superAdmin()->create();

        $ecole = Ecole::factory()->create([
            'plan' => Ecole::PLAN_GRATUIT,
            'statut' => Ecole::STATUT_EXPIRE,
        ]);
        $this->admin = User::factory()->admin()->create(['ecole_id' => $ecole->id]);

        $this->paydunya = $this->partialMock(PayDunyaService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createInvoice')->andReturnUsing(
                fn (Facture $facture) => $this->fausseFacturePayDunya('test_tok_'.$facture->id, $facture)
            );
            $mock->shouldReceive('confirmInvoice')->andReturnUsing(function (string $token) {
                $factureId = (int) str_replace('test_tok_', '', $token);

                return $this->statutPayDunya === 'completed'
                    ? $this->fausseFacturePayDunya($token, Facture::withoutGlobalScopes()->find($factureId))
                    : null;
            });
        });
    }

    public function test_une_ecole_s_abonne_et_paie_via_paydunya(): void
    {
        $this->paydunya->shouldReceive('configureRedistribution')->once()->with('98646779', 25000.0)->andReturnTrue();
        config(['services.paydunya.recipient_phone' => '98646779']);

        // Cliquer deux fois sur « S'abonner » ne crée qu'une seule facture
        $this->actingAs($this->admin)->post('/abonnement/souscrire')->assertRedirect();
        $this->post('/abonnement/souscrire')->assertRedirect();
        $facture = Facture::sole();
        $this->assertEquals(Ecole::TARIF_PREMIUM_2MOIS, $facture->montant);

        // Page de paiement : redirection vers PayDunya
        $this->get("/paydunya/initier/{$facture->id}")
            ->assertOk()
            ->assertSee('https://paydunya.test/checkout/test_tok_'.$facture->id, false);
        $this->assertSame('test_tok_'.$facture->id, $facture->fresh()->reference_transaction);

        // Retour de PayDunya après paiement
        $this->get("/paydunya/succes/{$facture->id}?token=test_tok_{$facture->id}")->assertOk();

        $facture->refresh();
        $ecole = $this->admin->ecole->fresh();
        $this->assertSame(Facture::STATUT_PAYEE, $facture->statut);
        $this->assertSame('paydunya', $facture->methode_paiement);
        $this->assertSame(Ecole::PLAN_PREMIUM, $ecole->plan);
        $this->assertTrue($ecole->aAccesPremium());
        $this->assertTrue($facture->abonnement->date_fin->between(now()->addMonths(2)->subDay(), now()->addMonths(2)->addDay()));

        // Rafraîchir la page ne rejoue ni l'activation ni la redistribution (once() ci-dessus)
        $this->get("/paydunya/succes/{$facture->id}?token=test_tok_{$facture->id}")->assertOk();
        $this->assertSame(1, $ecole->abonnements()->count());

        // L'école a maintenant accès aux fonctionnalités Premium (utilisateur rechargé comme à chaque requête réelle)
        $this->actingAs($this->admin->fresh())->get('/annonces')->assertOk();
    }

    public function test_un_paiement_non_abouti_n_active_rien(): void
    {
        $this->statutPayDunya = 'pending';
        $this->paydunya->shouldNotReceive('configureRedistribution');

        $this->actingAs($this->admin)->post('/abonnement/souscrire');
        $facture = Facture::sole();
        $this->get("/paydunya/initier/{$facture->id}");

        $this->get("/paydunya/succes/{$facture->id}?token=test_tok_{$facture->id}")
            ->assertOk()
            ->assertSee('Impossible de confirmer le paiement');

        $this->assertSame(Facture::STATUT_EN_ATTENTE, $facture->fresh()->statut);
        $this->assertFalse($this->admin->ecole->fresh()->aAccesPremium());
    }

    public function test_une_ecole_suspendue_peut_quand_meme_payer(): void
    {
        $this->admin->ecole->update(['statut' => Ecole::STATUT_SUSPENDU]);

        $this->actingAs($this->admin)->get('/dashboard')->assertRedirect('/abonnement');
        $this->post('/abonnement/souscrire')->assertRedirect();
        $this->get('/paydunya/initier/'.Facture::sole()->id)->assertOk();
    }

    public function test_le_webhook_confirme_le_paiement_si_le_navigateur_ne_revient_pas(): void
    {
        $this->paydunya->shouldReceive('configureRedistribution')->andReturnTrue();

        $this->actingAs($this->admin)->post('/abonnement/souscrire');
        $facture = Facture::sole();
        $this->get("/paydunya/initier/{$facture->id}");
        $this->post('/logout');

        // Fausse signature : refusée
        $this->post('/paydunya/webhook', ['data' => [
            'hash' => hash('sha512', 'mauvaise-cle'),
            'invoice' => ['token' => "test_tok_{$facture->id}"],
        ]])->assertStatus(400);
        $this->assertSame(Facture::STATUT_EN_ATTENTE, $facture->fresh()->statut);

        // Signature valide : paiement confirmé sans session utilisateur
        $this->post('/paydunya/webhook', ['data' => [
            'hash' => hash('sha512', self::MASTER_KEY),
            'status' => 'completed',
            'invoice' => ['token' => "test_tok_{$facture->id}"],
        ]])->assertOk()->assertJson(['status' => 'success']);

        $this->assertSame(Facture::STATUT_PAYEE, $facture->fresh()->statut);
        $this->assertTrue($this->admin->ecole->fresh()->aAccesPremium());
    }

    public function test_la_redistribution_est_desactivee_en_mode_test(): void
    {
        config(['services.paydunya.mode' => 'test', 'services.paydunya.redistribution_enabled' => true]);

        $this->assertFalse(app(PayDunyaService::class)->configureRedistribution('98646779', 25000));
    }

    private function fausseFacturePayDunya(string $token, ?Facture $facture): CheckoutInvoice
    {
        $invoice = Mockery::mock(CheckoutInvoice::class);
        $invoice->token = $token;
        $invoice->shouldReceive('getInvoiceUrl')->andReturn("https://paydunya.test/checkout/{$token}");
        $invoice->shouldReceive('getStatus')->andReturn('completed');
        $invoice->shouldReceive('getCustomData')->with('facture_id')->andReturn($facture?->id);
        $invoice->shouldReceive('getTotalAmount')->andReturn($facture?->montant);

        return $invoice;
    }
}
