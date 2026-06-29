<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreneauHoraireRequest;
use App\Models\Classe;
use App\Models\CreneauHoraire;
use App\Models\Matiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmploiDuTempsController extends Controller
{
    public function index(?Classe $classe = null): View
    {
        $this->authorize('viewAny', CreneauHoraire::class);

        $user = Auth::user();
        $classe ??= match (true) {
            $user->isEleve() => $user->eleve?->classe,
            $user->isEnseignant() => $user->enseignant?->classesPrincipales()->first(),
            default => Classe::orderBy('nom')->first(),
        };

        $creneaux = $classe
            ? CreneauHoraire::with(['matiere', 'enseignant'])
                ->where('classe_id', $classe->id)
                ->orderBy('jour_semaine')
                ->orderBy('heure_debut')
                ->get()
                ->groupBy('jour_semaine')
            : collect();

        return view('emploi-du-temps.index', [
            'classe' => $classe,
            'classes' => Classe::orderBy('nom')->get(),
            'creneaux' => $creneaux,
            'jours' => CreneauHoraire::JOURS,
            'matieres' => Matiere::orderBy('nom')->get(),
        ]);
    }

    public function store(CreneauHoraireRequest $request): RedirectResponse
    {
        $this->authorize('create', CreneauHoraire::class);

        CreneauHoraire::create($request->validated());

        return redirect()->route('emploi-du-temps.index', ['classe' => $request->classe_id])
            ->with('success', 'Créneau ajouté à l\'emploi du temps.');
    }

    public function destroy(CreneauHoraire $creneauHoraire): RedirectResponse
    {
        $this->authorize('delete', $creneauHoraire);

        $classeId = $creneauHoraire->classe_id;
        $creneauHoraire->delete();

        return redirect()->route('emploi-du-temps.index', ['classe' => $classeId])
            ->with('success', 'Créneau supprimé.');
    }
}
