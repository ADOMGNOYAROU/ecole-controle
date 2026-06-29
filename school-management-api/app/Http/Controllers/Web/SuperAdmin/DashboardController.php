<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Facture;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $debutMois = now()->startOfMonth();

        $stats = [
            'ecoles_total' => Ecole::count(),
            'ecoles_actives' => Ecole::where('statut', Ecole::STATUT_ACTIF)->count(),
            'ecoles_essai' => Ecole::where('statut', Ecole::STATUT_ESSAI)->count(),
            'ecoles_suspendues' => Ecole::where('statut', Ecole::STATUT_SUSPENDU)->count(),
            'nouvelles_ce_mois' => Ecole::where('created_at', '>=', $debutMois)->count(),
        ];

        $abonnementsActifs = \App\Models\Abonnement::where('statut', 'actif')->where('date_fin', '>=', now())->count();
        $mrrEstime = round(($abonnementsActifs * Ecole::TARIF_PREMIUM_TRIMESTRIEL) / 3);

        $revenuCeMois = Facture::where('statut', Facture::STATUT_PAYEE)
            ->where('payee_le', '>=', $debutMois)
            ->sum('montant');

        $facturesEnAttente = Facture::where('statut', Facture::STATUT_EN_ATTENTE)->count();
        $facturesEnRetard = Facture::where('statut', Facture::STATUT_EN_ATTENTE)
            ->where('date_echeance', '<', now())
            ->count();
        $montantEnAttente = Facture::whereIn('statut', [Facture::STATUT_EN_ATTENTE])->sum('montant');

        $dernieresEcoles = Ecole::withCount('users')->latest('created_at')->take(5)->get();

        $facturesUrgentes = Facture::with('ecole')
            ->where('statut', Facture::STATUT_EN_ATTENTE)
            ->orderBy('date_echeance')
            ->take(5)
            ->get();

        return view('super-admin.dashboard', [
            'stats' => $stats,
            'mrrEstime' => $mrrEstime,
            'revenuCeMois' => $revenuCeMois,
            'facturesEnAttente' => $facturesEnAttente,
            'facturesEnRetard' => $facturesEnRetard,
            'montantEnAttente' => $montantEnAttente,
            'dernieresEcoles' => $dernieresEcoles,
            'facturesUrgentes' => $facturesUrgentes,
        ]);
    }
}
