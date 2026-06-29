<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClasseRequest;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Services\RapportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ClasseController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Classe::class);

        $classes = Classe::with('anneeScolaire', 'enseignantPrincipal')
            ->withCount('eleves')
            ->orderBy('nom')
            ->paginate(15);

        return view('classes.index', compact('classes'));
    }

    public function rapport(RapportPdfService $rapportPdf): Response
    {
        $this->authorize('viewAny', Classe::class);

        $classes = Classe::with('anneeScolaire', 'enseignantPrincipal')->withCount('eleves')->orderBy('nom')->get();

        $lignes = $classes->map(fn (Classe $classe) => [
            $classe->nom,
            $classe->niveau ?? '—',
            $classe->eleves_count,
            $classe->enseignantPrincipal?->nomComplet() ?? 'Non assigné',
            $classe->anneeScolaire?->libelle ?? '—',
        ])->all();

        $pdf = $rapportPdf->listePdf(
            'Liste des classes',
            ['Classe', 'Niveau', 'Effectif', 'Enseignant principal', 'Année scolaire'],
            $lignes,
        );

        return $pdf->download('rapport-classes-'.now()->format('Y-m-d').'.pdf');
    }

    public function create(): View
    {
        $this->authorize('create', Classe::class);

        return view('classes.create', [
            'anneesScolaires' => AnneeScolaire::orderByDesc('date_debut')->get(),
            'enseignants' => Enseignant::orderBy('nom')->get(),
        ]);
    }

    public function store(ClasseRequest $request): RedirectResponse
    {
        $this->authorize('create', Classe::class);

        $classe = Classe::create($request->validated());

        return redirect()->route('classes.show', $classe)->with('success', 'Classe créée avec succès.');
    }

    public function show(Classe $classe): View
    {
        $this->authorize('view', $classe);

        $classe->load(['eleves' => fn ($q) => $q->orderBy('nom'), 'enseignants', 'matieres', 'creneauxHoraires.matiere', 'creneauxHoraires.enseignant']);

        return view('classes.show', compact('classe'));
    }

    public function edit(Classe $classe): View
    {
        $this->authorize('update', $classe);

        return view('classes.edit', [
            'classe' => $classe,
            'anneesScolaires' => AnneeScolaire::orderByDesc('date_debut')->get(),
            'enseignants' => Enseignant::orderBy('nom')->get(),
        ]);
    }

    public function update(ClasseRequest $request, Classe $classe): RedirectResponse
    {
        $this->authorize('update', $classe);

        $classe->update($request->validated());

        return redirect()->route('classes.show', $classe)->with('success', 'Classe mise à jour.');
    }

    public function destroy(Classe $classe): RedirectResponse
    {
        $this->authorize('delete', $classe);

        $classe->delete();

        return redirect()->route('classes.index')->with('success', 'Classe supprimée.');
    }
}
