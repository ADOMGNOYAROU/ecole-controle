<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnneeScolaireRequest;
use App\Models\AnneeScolaire;
use App\Models\Trimestre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnneeScolaireController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AnneeScolaire::class);

        $anneesScolaires = AnneeScolaire::withCount('trimestres', 'classes')->orderByDesc('date_debut')->get();
        $trimestres = Trimestre::with('anneeScolaire')->orderByDesc('date_debut')->get();

        return view('annees-scolaires.index', compact('anneesScolaires', 'trimestres'));
    }

    public function store(AnneeScolaireRequest $request): RedirectResponse
    {
        $this->authorize('create', AnneeScolaire::class);

        $this->appliquerActivation($request);

        AnneeScolaire::create($request->validated());

        return redirect()->route('annees-scolaires.index')->with('success', 'Année scolaire créée.');
    }

    public function update(AnneeScolaireRequest $request, AnneeScolaire $anneeScolaire): RedirectResponse
    {
        $this->authorize('update', $anneeScolaire);

        $this->appliquerActivation($request, $anneeScolaire);

        $anneeScolaire->update($request->validated());

        return redirect()->route('annees-scolaires.index')->with('success', 'Année scolaire mise à jour.');
    }

    public function destroy(AnneeScolaire $anneeScolaire): RedirectResponse
    {
        $this->authorize('delete', $anneeScolaire);

        $anneeScolaire->delete();

        return redirect()->route('annees-scolaires.index')->with('success', 'Année scolaire supprimée.');
    }

    private function appliquerActivation(AnneeScolaireRequest $request, ?AnneeScolaire $sauf = null): void
    {
        if ($request->boolean('active')) {
            AnneeScolaire::when($sauf, fn ($q) => $q->whereKeyNot($sauf->id))->update(['active' => false]);
        }
    }
}
