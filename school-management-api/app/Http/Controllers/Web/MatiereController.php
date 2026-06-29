<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\MatiereRequest;
use App\Models\Matiere;
use App\Services\RapportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MatiereController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Matiere::class);

        $matieres = Matiere::orderBy('nom')->paginate(20);

        return view('matieres.index', compact('matieres'));
    }

    public function rapport(RapportPdfService $rapportPdf): Response
    {
        $this->authorize('viewAny', Matiere::class);

        $matieres = Matiere::orderBy('nom')->get();

        $lignes = $matieres->map(fn (Matiere $matiere) => [
            $matiere->nom,
            $matiere->code ?? '—',
            $matiere->coefficient_defaut,
        ])->all();

        $pdf = $rapportPdf->listePdf(
            'Liste des matières',
            ['Matière', 'Code', 'Coefficient par défaut'],
            $lignes,
        );

        return $pdf->download('rapport-matieres-'.now()->format('Y-m-d').'.pdf');
    }

    public function create(): View
    {
        $this->authorize('create', Matiere::class);

        return view('matieres.create');
    }

    public function store(MatiereRequest $request): RedirectResponse
    {
        $this->authorize('create', Matiere::class);

        Matiere::create($request->validated());

        return redirect()->route('matieres.index')->with('success', 'Matière créée.');
    }

    public function edit(Matiere $matiere): View
    {
        $this->authorize('update', $matiere);

        return view('matieres.edit', compact('matiere'));
    }

    public function update(MatiereRequest $request, Matiere $matiere): RedirectResponse
    {
        $this->authorize('update', $matiere);

        $matiere->update($request->validated());

        return redirect()->route('matieres.index')->with('success', 'Matière mise à jour.');
    }

    public function destroy(Matiere $matiere): RedirectResponse
    {
        $this->authorize('delete', $matiere);

        $matiere->delete();

        return redirect()->route('matieres.index')->with('success', 'Matière supprimée.');
    }
}
