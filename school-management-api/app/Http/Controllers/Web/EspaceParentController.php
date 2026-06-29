<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Trimestre;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EspaceParentController extends Controller
{
    public function enfants(): View
    {
        $tuteur = Auth::user()->tuteur;
        abort_if(! $tuteur, 404);

        return view('mon-espace.enfants', ['enfants' => $tuteur->eleves()->with('classe')->get()]);
    }

    public function enfant(Eleve $eleve): View
    {
        $tuteur = Auth::user()->tuteur;
        abort_if(! $tuteur, 404);
        abort_unless($tuteur->eleves()->where('eleves.id', $eleve->id)->exists(), 403);

        $trimestre = Trimestre::actuel();

        return view('mon-espace.enfant-detail', [
            'eleve' => $eleve,
            'trimestre' => $trimestre,
            'donnees' => $trimestre ? $eleve->bulletinDonnees($trimestre->id) : null,
            'tauxPresence' => $trimestre ? $eleve->tauxPresenceTrimestre($trimestre->id) : null,
            'presences' => $eleve->presences()->latest('date')->take(15)->get(),
            'paiements' => $eleve->paiements()->latest('date_echeance')->get(),
        ]);
    }
}
