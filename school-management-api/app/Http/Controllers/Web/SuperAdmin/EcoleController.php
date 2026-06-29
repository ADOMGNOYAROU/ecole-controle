<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Eleve;
use App\Models\Enseignant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EcoleController extends Controller
{
    public function index(Request $request): View
    {
        $ecoles = Ecole::withCount('users')
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $terme = $request->recherche;
                $q->where(fn ($q2) => $q2->where('nom', 'like', "%{$terme}%")
                    ->orWhere('ville', 'like', "%{$terme}%"));
            })
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('plan'), fn ($q) => $q->where('plan', $request->plan))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('super-admin.ecoles.index', compact('ecoles'));
    }

    public function show(Ecole $ecole): View
    {
        $ecole->load(['abonnements' => fn ($q) => $q->orderByDesc('date_fin'), 'factures' => fn ($q) => $q->orderByDesc('created_at')]);

        $stats = [
            'eleves' => Eleve::where('ecole_id', $ecole->id)->count(),
            'enseignants' => Enseignant::where('ecole_id', $ecole->id)->count(),
            'classes' => Classe::where('ecole_id', $ecole->id)->count(),
            'utilisateurs' => $ecole->users()->count(),
        ];

        return view('super-admin.ecoles.show', [
            'ecole' => $ecole,
            'stats' => $stats,
            'abonnementActif' => $ecole->abonnementActif(),
        ]);
    }

    public function suspendre(Ecole $ecole): RedirectResponse
    {
        $ecole->update(['statut' => Ecole::STATUT_SUSPENDU]);

        return back()->with('success', "École {$ecole->nom} suspendue.");
    }

    public function activer(Ecole $ecole): RedirectResponse
    {
        $ecole->update(['statut' => Ecole::STATUT_ACTIF]);

        return back()->with('success', "École {$ecole->nom} réactivée.");
    }
}
