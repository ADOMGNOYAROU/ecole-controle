<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Trimestre;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EspaceEleveController extends Controller
{
    public function notes(): View
    {
        $eleve = Auth::user()->eleve;
        abort_if(! $eleve, 404);

        $trimestres = Trimestre::where('annee_scolaire_id', $eleve->classe?->annee_scolaire_id)
            ->orderByDesc('ordre')
            ->get();

        $trimestre = $trimestres->firstWhere('id', request('trimestre_id')) ?? Trimestre::actuel();

        return view('mon-espace.notes', [
            'eleve' => $eleve,
            'trimestres' => $trimestres,
            'trimestre' => $trimestre,
            'donnees' => $trimestre ? $eleve->bulletinDonnees($trimestre->id) : null,
        ]);
    }

    public function presences(): View
    {
        $eleve = Auth::user()->eleve;
        abort_if(! $eleve, 404);

        $presences = $eleve->presences()->latest('date')->paginate(25);

        return view('mon-espace.presences', compact('eleve', 'presences'));
    }

    public function paiements(): View
    {
        $eleve = Auth::user()->eleve;
        abort_if(! $eleve, 404);

        $paiements = $eleve->paiements()->latest('date_echeance')->get();

        return view('mon-espace.paiements', compact('eleve', 'paiements'));
    }
}
