<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkNoteRequest;
use App\Http\Requests\NoteRequest;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Trimestre;
use App\Services\RapportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Note::class);

        $notes = $this->filtrer($request)
            ->latest('date_evaluation')
            ->paginate(25)
            ->withQueryString();

        return view('notes.index', [
            'notes' => $notes,
            'classes' => Classe::orderBy('nom')->get(),
            'matieres' => Matiere::orderBy('nom')->get(),
            'trimestres' => Trimestre::orderByDesc('date_debut')->get(),
        ]);
    }

    public function rapport(Request $request, RapportPdfService $rapportPdf): Response
    {
        $this->authorize('viewAny', Note::class);

        $notes = $this->filtrer($request)->latest('date_evaluation')->get();

        $lignes = $notes->map(fn (Note $note) => [
            $note->eleve?->nomComplet() ?? '—',
            $note->matiere?->nom ?? '—',
            $note->classe?->nom ?? '—',
            $note->type,
            $note->noteSur20().'/20',
            $note->date_evaluation->format('d/m/Y'),
        ])->all();

        $pdf = $rapportPdf->listePdf(
            'Liste des notes',
            ['Élève', 'Matière', 'Classe', 'Type', 'Note', 'Date'],
            $lignes,
        );

        return $pdf->download('rapport-notes-'.now()->format('Y-m-d').'.pdf');
    }

    private function filtrer(Request $request)
    {
        $enseignant = Auth::user()->enseignant;

        return Note::with(['eleve', 'matiere', 'classe', 'trimestre'])
            ->when(! Auth::user()->isAdmin() && $enseignant, fn ($q) => $q->where('enseignant_id', $enseignant->id))
            ->when($request->filled('classe_id'), fn ($q) => $q->where('classe_id', $request->classe_id))
            ->when($request->filled('matiere_id'), fn ($q) => $q->where('matiere_id', $request->matiere_id))
            ->when($request->filled('trimestre_id'), fn ($q) => $q->where('trimestre_id', $request->trimestre_id));
    }

    public function create(): View
    {
        $this->authorize('create', Note::class);

        return view('notes.create', [
            'classes' => Classe::orderBy('nom')->get(),
            'matieres' => Matiere::orderBy('nom')->get(),
            'trimestres' => Trimestre::orderByDesc('date_debut')->get(),
            'enseignants' => \App\Models\Enseignant::orderBy('nom')->get(),
        ]);
    }

    public function store(NoteRequest $request): RedirectResponse
    {
        $this->authorize('create', Note::class);

        $enseignant = Auth::user()->enseignant;

        Note::create([
            ...$request->validated(),
            'enseignant_id' => $enseignant?->id ?? $request->input('enseignant_id'),
        ]);

        return redirect()->route('notes.index')->with('success', 'Note enregistrée.');
    }

    public function bulk(): View
    {
        $this->authorize('create', Note::class);

        return view('notes.bulk', [
            'classes' => Classe::orderBy('nom')->get(),
            'matieres' => Matiere::orderBy('nom')->get(),
            'trimestres' => Trimestre::orderByDesc('date_debut')->get(),
            'enseignants' => \App\Models\Enseignant::orderBy('nom')->get(),
        ]);
    }

    public function elevesPourSaisie(Classe $classe)
    {
        $this->authorize('create', Note::class);

        return response()->json(
            $classe->elevesActifs()->orderBy('nom')->get(['id', 'nom', 'prenom', 'matricule'])
        );
    }

    public function bulkStore(BulkNoteRequest $request): RedirectResponse
    {
        $this->authorize('create', Note::class);

        $enseignantId = Auth::user()->enseignant?->id ?? $request->input('enseignant_id');
        $donnees = $request->validated();

        foreach ($donnees['notes'] as $entry) {
            Note::updateOrCreate(
                [
                    'eleve_id' => $entry['eleve_id'],
                    'matiere_id' => $donnees['matiere_id'],
                    'classe_id' => $donnees['classe_id'],
                    'trimestre_id' => $donnees['trimestre_id'],
                    'type' => $donnees['type'],
                    'date_evaluation' => $donnees['date_evaluation'],
                ],
                [
                    'enseignant_id' => $enseignantId,
                    'valeur' => $entry['valeur'],
                    'bareme' => $donnees['bareme'],
                    'coefficient' => $donnees['coefficient'],
                ]
            );
        }

        return redirect()->route('notes.index')->with('success', 'Notes enregistrées pour la classe.');
    }

    public function edit(Note $note): View
    {
        $this->authorize('update', $note);

        return view('notes.edit', [
            'note' => $note,
            'classes' => Classe::orderBy('nom')->get(),
            'matieres' => Matiere::orderBy('nom')->get(),
            'trimestres' => Trimestre::orderByDesc('date_debut')->get(),
        ]);
    }

    public function update(NoteRequest $request, Note $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $note->update($request->validated());

        return redirect()->route('notes.index')->with('success', 'Note mise à jour.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note supprimée.');
    }

    public function reports(Request $request): View
    {
        $this->authorize('viewAny', Note::class);

        $trimestre = $request->filled('trimestre_id')
            ? Trimestre::find($request->trimestre_id)
            : Trimestre::actuel();

        $classes = Classe::with('elevesActifs')->orderBy('nom')->get();

        $rapportParClasse = $classes->map(function (Classe $classe) use ($trimestre) {
            $moyennes = $classe->elevesActifs
                ->map(fn ($eleve) => $trimestre ? $eleve->moyenneTrimestre($trimestre->id) : null)
                ->filter(fn ($m) => $m !== null);

            return [
                'classe' => $classe,
                'effectif' => $classe->elevesActifs->count(),
                'moyenne_classe' => $moyennes->isNotEmpty() ? round($moyennes->avg(), 2) : null,
            ];
        });

        return view('notes.reports', [
            'rapportParClasse' => $rapportParClasse,
            'trimestre' => $trimestre,
            'trimestres' => Trimestre::orderByDesc('date_debut')->get(),
        ]);
    }
}
