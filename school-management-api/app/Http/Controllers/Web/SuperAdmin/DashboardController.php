<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Ecole;
use App\Models\Facture;
use App\Services\SuiviEcoles;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(SuiviEcoles $suivi): View
    {
        $sante = $suivi->sante();

        $debutMois = now()->startOfMonth();

        $stats = [
            'ecoles_total' => Ecole::count(),
            'ecoles_actives' => Ecole::where('statut', Ecole::STATUT_ACTIF)->count(),
            'ecoles_essai' => Ecole::where('statut', Ecole::STATUT_ESSAI)->count(),
            'ecoles_suspendues' => Ecole::where('statut', Ecole::STATUT_SUSPENDU)->count(),
            'nouvelles_ce_mois' => Ecole::where('created_at', '>=', $debutMois)->count(),
        ];

        $abonnementsActifs = Abonnement::where('statut', 'actif')->where('date_fin', '>=', now())->count();
        // Un abonnement couvre 2 mois : revenu mensuel = tarif / 2
        $mrrEstime = round(($abonnementsActifs * Ecole::TARIF_PREMIUM_2MOIS) / 2);

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
            'sante' => $sante,
            'essaisQuiFinissent' => $suivi->essaisQuiFinissent(),
            'ecolesEndormies' => $suivi->ecolesEndormies($sante)->take(8),
            'ecolesFragiles' => Ecole::whereIn('id', $sante->filter(fn ($s) => $s['niveau'] !== 'bonne')->keys())
                ->where('statut', '!=', Ecole::STATUT_SUSPENDU)->get()
                ->sortBy(fn (Ecole $e) => $sante[$e->id]['score'])->take(8),
            'revenus' => $suivi->revenus12Mois(),
            'conversion' => $suivi->conversion(),
            'payDunya' => $suivi->payDunyaDuMois(),
        ]);
    }
}
