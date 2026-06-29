<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Facture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FactureController extends Controller
{
    public function index(Request $request): View
    {
        $factures = Facture::with('ecole')
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('ecole_id'), fn ($q) => $q->where('ecole_id', $request->ecole_id))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'en_attente' => Facture::where('statut', Facture::STATUT_EN_ATTENTE)->count(),
            'en_retard' => Facture::where('statut', Facture::STATUT_EN_ATTENTE)->where('date_echeance', '<', now())->count(),
            'payees_ce_mois' => Facture::where('statut', Facture::STATUT_PAYEE)->where('payee_le', '>=', now()->startOfMonth())->sum('montant'),
            'montant_en_attente' => Facture::where('statut', Facture::STATUT_EN_ATTENTE)->sum('montant'),
        ];

        return view('super-admin.factures.index', [
            'factures' => $factures,
            'stats' => $stats,
            'ecoles' => Ecole::orderBy('nom')->get(),
        ]);
    }

    /**
     * Confirmation manuelle de paiement. À remplacer par le traitement automatique
     * du webhook une fois l'API de paiement Mobile Money branchée.
     */
    public function confirmer(Request $request, Facture $facture): RedirectResponse
    {
        $request->validate([
            'methode_paiement' => ['required', 'in:flooz,tmoney,virement,especes,autre'],
            'reference_transaction' => ['nullable', 'string', 'max:100'],
        ]);

        $facture->confirmerPaiement(
            Auth::user(),
            $request->methode_paiement,
            $request->reference_transaction
        );

        return back()->with('success', "Facture #{$facture->id} confirmée comme payée. Abonnement Premium activé pour {$facture->ecole->nom}.");
    }
}
