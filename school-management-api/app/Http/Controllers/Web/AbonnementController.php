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
            'tarif' => Ecole::TARIF_PREMIUM_2MOIS,
        ]);
    }

    /**
     * Crée une facture "en attente" et redirige vers PayDunya pour le paiement.
     */
    public function souscrire(): RedirectResponse
    {
        $ecole = Auth::user()->ecole;

        // Réutilise la facture en attente au bon tarif plutôt que d'en créer une à chaque clic
        $facture = Facture::firstOrCreate([
            'ecole_id' => $ecole->id,
            'montant' => Ecole::TARIF_PREMIUM_2MOIS,
            'statut' => Facture::STATUT_EN_ATTENTE,
        ], [
            'date_echeance' => now()->addDays(7),
        ]);

        return redirect()->route('paydunya.initiate', $facture);
    }
}
