<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkPresenceRequest;
use App\Http\Requests\PresenceRequest;
use App\Models\Classe;
use App\Models\Presence;
use App\Models\Trimestre;
use App\Services\RapportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PresenceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Presence::class);

        $presences = $this->filtrer($request)
            ->latest('date')
            ->paginate(25)
            ->withQueryString();

        return view('presences.index', [
            'presences' => $presences,
            'classes' => Classe::orderBy('nom')->get(),
        ]);
    }

    public function rapport(Request $request, RapportPdfService $rapportPdf): Response
    {
        $this->authorize('viewAny', Presence::class);

        $presences = $this->filtrer($request)->latest('date')->get();

        $lignes = $presences->map(fn (Presence $presence) => [
            $presence->eleve?->nomComplet() ?? '—',
            $presence->classe?->nom ?? '—',
            $presence->date->format('d/m/Y'),
            ucfirst($presence->statut),
            $presence->motif ?? '—',
        ])->all();

        $pdf = $rapportPdf->listePdf(
            'Liste des présences',
            ['Élève', 'Classe', 'Date', 'Statut', 'Motif'],
            $lignes,
        );

        return $pdf->download('rapport-presences-'.now()->format('Y-m-d').'.pdf');
    }

    private function filtrer(Request $request)
    {
        $enseignant = Auth::user()->enseignant;

        return Presence::with(['eleve', 'classe'])
            ->when(! Auth::user()->isAdmin() && $enseignant, fn ($q) => $q->where('enseignant_id', $enseignant->id))
            ->when($request->filled('classe_id'), fn ($q) => $q->where('classe_id', $request->classe_id))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('date', $request->date));
    }

    public function create(): View
    {
        $this->authorize('create', Presence::class);

        return view('presences.create', ['classes' => Classe::orderBy('nom')->get()]);
    }

    public function store(PresenceRequest $request): RedirectResponse
    {
        $this->authorize('create', Presence::class);

        Presence::updateOrCreate(
            ['eleve_id' => $request->eleve_id, 'date' => $request->date],
            [
                ...$request->validated(),
                'enseignant_id' => Auth::user()->enseignant?->id,
                'trimestre_id' => $request->trimestre_id ?? Trimestre::actuel()?->id,
            ]
        );

        return redirect()->route('presences.index')->with('success', 'Présence enregistrée.');
    }

    public function bulk(): View
    {
        $this->authorize('create', Presence::class);

        return view('presences.bulk', ['classes' => Classe::orderBy('nom')->get()]);
    }

    public function elevesPourAppel(Classe $classe)
    {
        $this->authorize('create', Presence::class);

        return response()->json(
            $classe->elevesActifs()->orderBy('nom')->get(['id', 'nom', 'prenom', 'matricule'])
        );
    }

    public function bulkStore(BulkPresenceRequest $request): RedirectResponse
    {
        $this->authorize('create', Presence::class);

        $donnees = $request->validated();
        $enseignant = Auth::user()->enseignant;
        $trimestreId = $donnees['trimestre_id'] ?? Trimestre::actuel()?->id;

        foreach ($donnees['presences'] as $entry) {
            Presence::updateOrCreate(
                ['eleve_id' => $entry['eleve_id'], 'date' => $donnees['date']],
                [
                    'classe_id' => $donnees['classe_id'],
                    'enseignant_id' => $enseignant?->id,
                    'trimestre_id' => $trimestreId,
                    'statut' => $entry['statut'],
                    'motif' => $entry['motif'] ?? null,
                ]
            );
        }

        return redirect()->route('presences.index')->with('success', 'Présences enregistrées pour la classe.');
    }

    public function edit(Presence $presence): View
    {
        $this->authorize('update', $presence);

        return view('presences.edit', [
            'presence' => $presence,
            'classes' => Classe::orderBy('nom')->get(),
        ]);
    }

    public function update(PresenceRequest $request, Presence $presence): RedirectResponse
    {
        $this->authorize('update', $presence);

        $presence->update($request->validated());

        return redirect()->route('presences.index')->with('success', 'Présence mise à jour.');
    }

    public function destroy(Presence $presence): RedirectResponse
    {
        $this->authorize('delete', $presence);

        $presence->delete();

        return redirect()->route('presences.index')->with('success', 'Présence supprimée.');
    }
}
