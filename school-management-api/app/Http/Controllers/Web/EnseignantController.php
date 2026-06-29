<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnseignantRequest;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Services\RapportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EnseignantController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Enseignant::class);

        $enseignants = Enseignant::withCount('classesPrincipales')
            ->orderBy('nom')
            ->paginate(20);

        return view('enseignants.index', compact('enseignants'));
    }

    public function rapport(RapportPdfService $rapportPdf): Response
    {
        $this->authorize('viewAny', Enseignant::class);

        $enseignants = Enseignant::withCount('classesPrincipales')->orderBy('nom')->get();

        $lignes = $enseignants->map(fn (Enseignant $enseignant) => [
            $enseignant->nomComplet(),
            $enseignant->specialite ?? '—',
            $enseignant->telephone ?? '—',
            $enseignant->classes_principales_count,
        ])->all();

        $pdf = $rapportPdf->listePdf(
            'Liste des enseignants',
            ['Nom complet', 'Spécialité', 'Téléphone', 'Classes principales'],
            $lignes,
        );

        return $pdf->download('rapport-enseignants-'.now()->format('Y-m-d').'.pdf');
    }

    public function create(): View
    {
        $this->authorize('create', Enseignant::class);

        return view('enseignants.create', [
            'matieres' => Matiere::orderBy('nom')->get(),
            'classes' => Classe::orderBy('nom')->get(),
        ]);
    }

    public function store(EnseignantRequest $request): RedirectResponse
    {
        $this->authorize('create', Enseignant::class);

        $enseignant = Enseignant::create($request->safe()->except(['matieres', 'classes']));

        $this->syncAffectations($enseignant, $request->input('matieres', []), $request->input('classes', []));

        return redirect()->route('enseignants.show', $enseignant)->with('success', 'Enseignant ajouté avec succès.');
    }

    public function show(Enseignant $enseignant): View
    {
        $this->authorize('view', $enseignant);

        $enseignant->load(['classesPrincipales', 'matieres', 'classes', 'responsabilites']);

        return view('enseignants.show', compact('enseignant'));
    }

    public function edit(Enseignant $enseignant): View
    {
        $this->authorize('update', $enseignant);

        return view('enseignants.edit', [
            'enseignant' => $enseignant,
            'matieres' => Matiere::orderBy('nom')->get(),
            'classes' => Classe::orderBy('nom')->get(),
            'matiereIds' => $enseignant->matieres()->pluck('matieres.id')->all(),
            'classeIds' => $enseignant->classes()->pluck('classes.id')->all(),
        ]);
    }

    public function update(EnseignantRequest $request, Enseignant $enseignant): RedirectResponse
    {
        $this->authorize('update', $enseignant);

        $enseignant->update($request->safe()->except(['matieres', 'classes']));

        $this->syncAffectations($enseignant, $request->input('matieres', []), $request->input('classes', []));

        return redirect()->route('enseignants.show', $enseignant)->with('success', 'Enseignant mis à jour.');
    }

    public function destroy(Enseignant $enseignant): RedirectResponse
    {
        $this->authorize('delete', $enseignant);

        $enseignant->delete();

        return redirect()->route('enseignants.index')->with('success', 'Enseignant supprimé.');
    }

    /**
     * Associe l'enseignant à chaque combinaison matière x classe sélectionnée.
     */
    private function syncAffectations(Enseignant $enseignant, array $matiereIds, array $classeIds): void
    {
        $enseignant->matieres()->newPivotStatement()->where('enseignant_id', $enseignant->id)->delete();

        foreach ($matiereIds as $matiereId) {
            foreach ($classeIds as $classeId) {
                $enseignant->matieres()->attach($matiereId, ['classe_id' => $classeId]);
            }
        }
    }
}
