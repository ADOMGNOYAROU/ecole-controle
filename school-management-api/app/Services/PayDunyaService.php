<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Paydunya\Checkout\CheckoutInvoice;
use Paydunya\Checkout\Store;
use Paydunya\Setup;

class PayDunyaService
{
    private Store $store;

    public function __construct()
    {
        $this->configurePayDunya();
    }

    private function configurePayDunya(): void
    {
        $masterKey = config('services.paydunya.master_key');
        $publicKey = config('services.paydunya.public_key');
        $privateKey = config('services.paydunya.private_key');
        $token = config('services.paydunya.token');
        $mode = config('services.paydunya.mode', 'test');

        Setup::setMasterKey($masterKey);
        Setup::setPublicKey($publicKey);
        Setup::setPrivateKey($privateKey);
        Setup::setToken($token);
        Setup::setMode($mode);

        $this->store = new Store;
        $this->store->setName(config('app.name', 'École Manager'));
        $this->store->setTagline('Gestion scolaire simplifiée');
        $this->store->setPhoneNumber('+228 90 00 00 00');
        $this->store->setPostalAddress('Lomé, Togo');
        $this->store->setWebsiteUrl(config('app.url'));
        $this->store->setLogoUrl(config('app.url').'/logo.png');
    }

    public function createInvoice(Facture $facture, string $returnUrl, string $cancelUrl, string $callbackUrl): ?CheckoutInvoice
    {
        try {
            $invoice = new CheckoutInvoice;

            $invoice->addItem(
                'Abonnement Premium - '.ucfirst($facture->ecole->nom),
                1,
                $facture->montant,
                $facture->montant,
                "Facture #{$facture->id}"
            );

            $invoice->setTotalAmount($facture->montant);

            $invoice->setDescription(
                "Paiement de l'abonnement Premium pour l'école {$facture->ecole->nom}. Facture #{$facture->id}"
            );

            // return_url : redirection du navigateur après paiement (PayDunya ajoute ?token=...)
            // callback_url : notification IPN serveur à serveur (nécessite une URL publique)
            $invoice->setReturnUrl($returnUrl);
            $invoice->setCancelUrl($cancelUrl);
            $invoice->setCallbackUrl($callbackUrl);

            $invoice->addCustomData('facture_id', $facture->id);
            $invoice->addCustomData('ecole_id', $facture->ecole_id);

            // Configuration de la redistribution automatique (PER)
            // PayDunya PER doit être configuré dans le tableau de bord PayDunya
            // Nous stockons les informations pour référence
            $recipientPhone = config('services.paydunya.recipient_phone', '98646779');
            $invoice->addCustomData('recipient_phone', $recipientPhone);
            $invoice->addCustomData('auto_redistribute', true);

            if ($invoice->create()) {
                return $invoice;
            }

            Log::error('PayDunya invoice creation failed', [
                'facture_id' => $facture->id,
                'response' => $invoice->response_text ?? 'unknown',
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('PayDunya service error', [
                'facture_id' => $facture->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function confirmInvoice(string $token): ?CheckoutInvoice
    {
        try {
            $invoice = new CheckoutInvoice;

            if ($invoice->confirm($token)) {
                return $invoice;
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('PayDunya invoice confirmation error', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Vérifie une notification IPN : PayDunya envoie data[hash] = sha512(master_key).
     */
    public function verifyWebhook(array $data): bool
    {
        $masterKey = (string) config('services.paydunya.master_key');

        return $masterKey !== ''
            && isset($data['hash'], $data['invoice']['token'])
            && hash_equals(hash('sha512', $masterKey), (string) $data['hash']);
    }

    /**
     * Confirme le paiement auprès de PayDunya puis active l'abonnement.
     * Idempotent : peut être appelé par la page de retour ET par l'IPN sans double traitement.
     */
    public function finaliserPaiement(Facture $facture, string $token): bool
    {
        // Le token peut différer de reference_transaction si le paiement a été relancé :
        // l'API PayDunya (custom_data facture_id) fait foi.
        if ($facture->statut === Facture::STATUT_PAYEE) {
            return true;
        }

        $invoice = $this->confirmInvoice($token);

        if (! $invoice || $invoice->getStatus() !== 'completed'
            || (int) $invoice->getCustomData('facture_id') !== $facture->id
            || (float) $invoice->getTotalAmount() < (float) $facture->montant) {
            Log::warning('PayDunya paiement non confirmé', [
                'facture_id' => $facture->id,
                'token' => $token,
                'status' => $invoice?->getStatus(),
            ]);

            return false;
        }

        $superAdmin = User::where('role', 'super_admin')->first();

        if (! $superAdmin) {
            Log::error('PayDunya : aucun super_admin pour confirmer la facture', ['facture_id' => $facture->id]);

            return false;
        }

        $confirmee = DB::transaction(function () use ($facture, $superAdmin, $token) {
            $verrouillee = Facture::whereKey($facture->id)->lockForUpdate()->first();

            if (! in_array($verrouillee->statut, [Facture::STATUT_EN_ATTENTE, Facture::STATUT_EN_RETARD], true)) {
                return false;
            }

            $verrouillee->confirmerPaiement($superAdmin, 'paydunya', $token);

            return true;
        });

        if ($confirmee) {
            $facture->refresh();

            $recipientPhone = config('services.paydunya.recipient_phone');
            if ($recipientPhone) {
                $this->configureRedistribution($recipientPhone, (float) $facture->montant);
            }
        }

        return true;
    }

    /**
     * Reverse le montant encaissé vers le numéro Mobile Money du destinataire
     * via l'API Déboursement v2 de PayDunya (PER).
     *
     * Cette API n'accepte QUE les clés de production : elle est donc ignorée en mode test,
     * et doit être activée explicitement avec PAYDUNYA_REDISTRIBUTION_ENABLED=true.
     */
    public function configureRedistribution(string $recipientPhone, float $amount): bool
    {
        $contexte = [
            'recipient_name' => config('services.paydunya.recipient_name'),
            'recipient_phone' => $recipientPhone,
            'amount' => $amount,
        ];

        if (! config('services.paydunya.redistribution_enabled') || config('services.paydunya.mode') !== 'live') {
            Log::info('PayDunya redistribution ignorée (désactivée ou mode test)', $contexte);

            return false;
        }

        try {
            $client = Http::withHeaders([
                'PAYDUNYA-MASTER-KEY' => config('services.paydunya.master_key'),
                'PAYDUNYA-PRIVATE-KEY' => config('services.paydunya.private_key'),
                'PAYDUNYA-TOKEN' => config('services.paydunya.token'),
            ])->acceptJson()->timeout(30);

            $demande = $client->post('https://app.paydunya.com/api/v2/disburse/get-invoice', [
                'account_alias' => $recipientPhone,
                'amount' => (int) round($amount),
                'withdraw_mode' => config('services.paydunya.recipient_withdraw_mode'),
                'callback_url' => route('paydunya.webhook'),
            ])->json();

            if (($demande['response_code'] ?? null) !== '00' || empty($demande['disburse_token'])) {
                Log::error('PayDunya redistribution refusée (get-invoice)', $contexte + ['response' => $demande]);

                return false;
            }

            $envoi = $client->post('https://app.paydunya.com/api/v2/disburse/submit-invoice', [
                'disburse_invoice' => $demande['disburse_token'],
            ])->json();

            if (($envoi['response_code'] ?? null) !== '00') {
                Log::error('PayDunya redistribution refusée (submit-invoice)', $contexte + ['response' => $envoi]);

                return false;
            }

            Log::info('PayDunya redistribution effectuée', $contexte + [
                'status' => $envoi['status'] ?? null,
                'transaction_id' => $envoi['transaction_id'] ?? null,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('PayDunya redistribution error', $contexte + ['error' => $e->getMessage()]);

            return false;
        }
    }
}
