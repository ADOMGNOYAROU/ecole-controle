<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\TuteurRequest;
use App\Models\Eleve;
use App\Models\Tuteur;
use App\Services\RapportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TuteurController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Tuteur::class);

        $tuteurs = Tuteur::withCount('eleves')->orderBy('nom')->paginate(20);

        return view('tuteurs.index', compact('tuteurs'));
    }

    public function rapport(RapportPdfService $rapportPdf): Response
    {
        $this->authorize('viewAny', Tuteur::class);

        $tuteurs = Tuteur::withCount('eleves')->orderBy('nom')->get();

        $lignes = $tuteurs->map(fn (Tuteur $tuteur) => [
            $tuteur->nomComplet(),
            $tuteur->telephone,
            $tuteur->email ?? '—',
            $tuteur->eleves_count,
        ])->all();

        $pdf = $rapportPdf->listePdf(
            'Liste des tuteurs / parents',
            ['Nom complet', 'Téléphone', 'Email', "Nombre d'enfants"],
            $lignes,
        );

        return $pdf->download('rapport-tuteurs-'.now()->format('Y-m-d').'.pdf');
    }

    public function create(): View
    {
        $this->authorize('create', Tuteur::class);

        return view('tuteurs.create', ['eleves' => Eleve::orderBy('nom')->get()]);
    }

    public function store(TuteurRequest $request): RedirectResponse
    {
        $this->authorize('create', Tuteur::class);

        $tuteur = Tuteur::create($request->safe()->except('eleves'));

        $this->syncEnfants($tuteur, $request->input('eleves', []));

        return redirect()->route('tuteurs.show', $tuteur)->with('success', 'Tuteur ajouté avec succès.');
    }

    public function show(Tuteur $tuteur): View
    {
        $this->authorize('view', $tuteur);

        $tuteur->load('eleves.classe');

        return view('tuteurs.show', compact('tuteur'));
    }

    public function edit(Tuteur $tuteur): View
    {
        $this->authorize('update', $tuteur);

        return view('tuteurs.edit', [
            'tuteur' => $tuteur,
            'eleves' => Eleve::orderBy('nom')->get(),
            'liens' => $tuteur->eleves()->pluck('eleve_tuteur.lien_parente', 'eleves.id'),
        ]);
    }

    public function update(TuteurRequest $request, Tuteur $tuteur): RedirectResponse
    {
        $this->authorize('update', $tuteur);

        $tuteur->update($request->safe()->except('eleves'));

        $this->syncEnfants($tuteur, $request->input('eleves', []));

        return redirect()->route('tuteurs.show', $tuteur)->with('success', 'Tuteur mis à jour.');
    }

    public function destroy(Tuteur $tuteur): RedirectResponse
    {
        $this->authorize('delete', $tuteur);

        $tuteur->delete();

        return redirect()->route('tuteurs.index')->with('success', 'Tuteur supprimé.');
    }

    private function syncEnfants(Tuteur $tuteur, array $eleves): void
    {
        $pivotData = [];

        foreach ($eleves as $entry) {
            if (! empty($entry['id'])) {
                $pivotData[$entry['id']] = [
                    'lien_parente' => $entry['lien_parente'] ?? 'Tuteur',
                    'contact_principal' => ! empty($entry['contact_principal']),
                ];
            }
        }

        $tuteur->eleves()->sync($pivotData);
    }
}
