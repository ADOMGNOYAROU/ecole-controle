<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Services\PayDunyaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PayDunyaController extends Controller
{
    public function __construct(
        private PayDunyaService $paydunyaService
    ) {}

    public function initiate(Facture $facture): View
    {
        $this->authorize('update', $facture);

        if (! in_array($facture->statut, [Facture::STATUT_EN_ATTENTE, Facture::STATUT_EN_RETARD], true)) {
            return view('paydunya.error', [
                'message' => 'Cette facture a déjà été traitée.',
            ]);
        }

        $invoice = $this->paydunyaService->createInvoice(
            $facture,
            route('paydunya.success', ['facture' => $facture->id]),
            route('paydunya.cancel', ['facture' => $facture->id]),
            route('paydunya.webhook'),
        );

        if (! $invoice) {
            return view('paydunya.error', [
                'message' => 'Impossible de créer la facture PayDunya. Veuillez réessayer.',
            ]);
        }

        $facture->update([
            'reference_transaction' => $invoice->token,
        ]);

        return view('paydunya.initiate', [
            'invoiceUrl' => $invoice->getInvoiceUrl(),
            'facture' => $facture,
        ]);
    }

    public function success(Request $request, Facture $facture): View
    {
        $this->authorize('update', $facture);

        $token = $request->input('token') ?: $facture->reference_transaction;

        if (! $token) {
            return view('paydunya.error', [
                'message' => 'Token de transaction manquant.',
            ]);
        }

        if (! $this->paydunyaService->finaliserPaiement($facture, $token)) {
            return view('paydunya.error', [
                'message' => 'Impossible de confirmer le paiement. Si vous avez été débité, veuillez contacter le support.',
            ]);
        }

        return view('paydunya.success', [
            'facture' => $facture->fresh(),
            'receipt' => $token,
        ]);
    }

    public function cancel(Facture $facture): View
    {
        $this->authorize('update', $facture);

        return view('paydunya.cancel', [
            'facture' => $facture,
        ]);
    }

    /**
     * IPN PayDunya : POST form-encoded avec les champs dans data[...].
     */
    public function webhook(Request $request)
    {
        $data = $request->input('data', []);

        if (! is_array($data) || ! $this->paydunyaService->verifyWebhook($data)) {
            Log::warning('PayDunya webhook verification failed');

            return response()->json(['status' => 'error'], 400);
        }

        $token = $data['invoice']['token'];
        $facture = Facture::where('reference_transaction', $token)->first()
            ?? Facture::find($data['custom_data']['facture_id'] ?? null);

        if (! $facture) {
            Log::warning('PayDunya webhook : facture introuvable', ['token' => $token]);

            return response()->json(['status' => 'ignored']);
        }

        // Le statut est revérifié auprès de l'API PayDunya, on ne se fie pas au contenu de l'IPN
        $this->paydunyaService->finaliserPaiement($facture, $token);

        return response()->json(['status' => 'success']);
    }
}
