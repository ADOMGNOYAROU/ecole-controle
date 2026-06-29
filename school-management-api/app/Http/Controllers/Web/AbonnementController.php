<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Facture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AbonnementController extends Controller
{
    public function index(): View
    {
        $ecole = Auth::user()->ecole;

        return view('abonnement.index', [
            'ecole' => $ecole,
            'factures' => $ecole->factures()->latest('date_echeance')->get(),
            'tarif' => Ecole::TARIF_PREMIUM_TRIMESTRIEL,
        ]);
    }

    /**
     * Crée une facture "en attente" et affiche les instructions de paiement Mobile
     * Money. Tant que l'API de paiement n'est pas branchée, c'est le super-admin qui
     * confirme manuellement la réception du paiement (voir SuperAdmin\FactureController).
     */
    public function souscrire(): RedirectResponse
    {
        $ecole = Auth::user()->ecole;

        $facture = Facture::create([
            'ecole_id' => $ecole->id,
            'montant' => Ecole::TARIF_PREMIUM_TRIMESTRIEL,
            'date_echeance' => now()->addDays(7),
            'statut' => Facture::STATUT_EN_ATTENTE,
        ]);

        return redirect()->route('abonnement.index')
            ->with('success', "Facture #{$facture->id} créée (15 000 FCFA). Effectuez le paiement par Mobile Money et communiquez la référence à l'administration pour activation — l'intégration automatique arrive prochainement.");
    }
}
