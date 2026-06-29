<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaiementRequest;
use App\Models\AnneeScolaire;
use App\Models\Eleve;
use App\Models\Paiement;
use App\Services\RapportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PaiementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Paiement::class);

        $paiements = $this->filtrer($request)
            ->latest('date_echeance')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total_attendu' => Paiement::sum('montant'),
            'total_collecte' => Paiement::sum('montant_paye'),
            'en_retard' => Paiement::where('statut', '!=', Paiement::STATUT_PAYE)->where('date_echeance', '<', now())->count(),
        ];

        return view('paiements.index', compact('paiements', 'stats'));
    }

    public function rapport(Request $request, RapportPdfService $rapportPdf): Response
    {
        $this->authorize('viewAny', Paiement::class);

        $paiements = $this->filtrer($request)->latest('date_echeance')->get();

        $lignes = $paiements->map(fn (Paiement $paiement) => [
            $paiement->eleve?->nomComplet() ?? '—',
            $paiement->type,
            number_format($paiement->montant, 0, ',', ' ').' FCFA',
            number_format($paiement->montant_paye, 0, ',', ' ').' FCFA',
            $paiement->date_echeance->format('d/m/Y'),
            ucfirst($paiement->statut),
        ])->all();

        $pdf = $rapportPdf->listePdf(
            'Liste des paiements',
            ['Élève', 'Type', 'Montant', 'Payé', 'Échéance', 'Statut'],
            $lignes,
        );

        return $pdf->download('rapport-paiements-'.now()->format('Y-m-d').'.pdf');
    }

    private function filtrer(Request $request)
    {
        return Paiement::with('eleve')
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('eleve_id'), fn ($q) => $q->where('eleve_id', $request->eleve_id));
    }

    public function create(): View
    {
        $this->authorize('create', Paiement::class);

        return view('paiements.create', [
            'eleves' => Eleve::orderBy('nom')->get(),
            'anneesScolaires' => AnneeScolaire::orderByDesc('date_debut')->get(),
        ]);
    }

    public function store(PaiementRequest $request): RedirectResponse
    {
        $this->authorize('create', Paiement::class);

        $donnees = $request->validated();
        $donnees['statut'] = $this->calculerStatut($donnees);

        Paiement::create($donnees);

        return redirect()->route('paiements.index')->with('success', 'Paiement enregistré.');
    }

    public function edit(Paiement $paiement): View
    {
        $this->authorize('update', $paiement);

        return view('paiements.edit', [
            'paiement' => $paiement,
            'eleves' => Eleve::orderBy('nom')->get(),
            'anneesScolaires' => AnneeScolaire::orderByDesc('date_debut')->get(),
        ]);
    }

    public function update(PaiementRequest $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('update', $paiement);

        $donnees = $request->validated();
        $donnees['statut'] = $this->calculerStatut($donnees);

        $paiement->update($donnees);

        return redirect()->route('paiements.index')->with('success', 'Paiement mis à jour.');
    }

    public function destroy(Paiement $paiement): RedirectResponse
    {
        $this->authorize('delete', $paiement);

        $paiement->delete();

        return redirect()->route('paiements.index')->with('success', 'Paiement supprimé.');
    }

    private function calculerStatut(array $donnees): string
    {
        $paye = (float) ($donnees['montant_paye'] ?? 0);
        $montant = (float) $donnees['montant'];

        if ($paye >= $montant && $montant > 0) {
            return Paiement::STATUT_PAYE;
        }

        if ($paye > 0) {
            return Paiement::STATUT_PARTIEL;
        }

        return now()->gt($donnees['date_echeance']) ? Paiement::STATUT_RETARD : Paiement::STATUT_EN_ATTENTE;
    }
}
