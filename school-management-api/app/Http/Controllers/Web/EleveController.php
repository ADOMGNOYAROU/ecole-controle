<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EleveRequest;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Trimestre;
use App\Services\RapportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EleveController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Eleve::class);

        $eleves = $this->filtrer($request)
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('eleves.index', [
            'eleves' => $eleves,
            'classes' => Classe::orderBy('nom')->get(),
        ]);
    }

    public function rapport(Request $request, RapportPdfService $rapportPdf): Response
    {
        $this->authorize('viewAny', Eleve::class);

        $eleves = $this->filtrer($request)->orderBy('nom')->get();

        $lignes = $eleves->map(fn (Eleve $eleve) => [
            $eleve->matricule,
            $eleve->nomComplet(),
            $eleve->classe?->nom ?? 'Sans classe',
            $eleve->sexe === 'M' ? 'Masculin' : 'Féminin',
            $eleve->statut,
        ])->all();

        $pdf = $rapportPdf->listePdf(
            'Liste des élèves',
            ['Matricule', 'Nom complet', 'Classe', 'Sexe', 'Statut'],
            $lignes,
        );

        return $pdf->download('rapport-eleves-'.now()->format('Y-m-d').'.pdf');
    }

    private function filtrer(Request $request)
    {
        return Eleve::with('classe')
            ->when($request->filled('classe_id'), fn ($q) => $q->where('classe_id', $request->classe_id))
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $terme = $request->recherche;
                $q->where(fn ($q2) => $q2->where('nom', 'like', "%{$terme}%")
                    ->orWhere('prenom', 'like', "%{$terme}%")
                    ->orWhere('matricule', 'like', "%{$terme}%"));
            });
    }

    public function create(): View
    {
        $this->authorize('create', Eleve::class);

        return view('eleves.create', ['classes' => Classe::orderBy('nom')->get()]);
    }

    public function store(EleveRequest $request): RedirectResponse
    {
        $this->authorize('create', Eleve::class);

        $eleve = Eleve::create($request->validated());

        return redirect()->route('eleves.show', $eleve)->with('success', 'Élève inscrit avec succès.');
    }

    public function show(Eleve $eleve): View
    {
        $this->authorize('view', $eleve);

        $trimestre = Trimestre::actuel();
        $eleve->load(['classe', 'tuteurs']);

        return view('eleves.show', [
            'eleve' => $eleve,
            'trimestre' => $trimestre,
            'moyenne' => $trimestre ? $eleve->moyenneTrimestre($trimestre->id) : null,
            'tauxPresence' => $trimestre ? $eleve->tauxPresenceTrimestre($trimestre->id) : null,
            'dernieresNotes' => $eleve->notes()->with('matiere')->latest()->take(10)->get(),
        ]);
    }

    public function edit(Eleve $eleve): View
    {
        $this->authorize('update', $eleve);

        return view('eleves.edit', [
            'eleve' => $eleve,
            'classes' => Classe::orderBy('nom')->get(),
        ]);
    }

    public function update(EleveRequest $request, Eleve $eleve): RedirectResponse
    {
        $this->authorize('update', $eleve);

        $eleve->update($request->validated());

        return redirect()->route('eleves.show', $eleve)->with('success', 'Élève mis à jour.');
    }

    public function destroy(Eleve $eleve): RedirectResponse
    {
        $this->authorize('delete', $eleve);

        $eleve->delete();

        return redirect()->route('eleves.index')->with('success', 'Élève supprimé.');
    }
}
