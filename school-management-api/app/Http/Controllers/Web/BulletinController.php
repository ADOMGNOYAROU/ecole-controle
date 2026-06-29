<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Trimestre;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulletinController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Bulletin::class);

        $classes = $this->classesAutorisees();

        $bulletins = Bulletin::with(['eleve.classe', 'trimestre'])
            ->whereHas('eleve', fn ($q) => $q->whereIn('classe_id', $classes->pluck('id')))
            ->when($request->filled('classe_id'), fn ($q) => $q->whereHas('eleve', fn ($q2) => $q2->where('classe_id', $request->classe_id)))
            ->when($request->filled('trimestre_id'), fn ($q) => $q->where('trimestre_id', $request->trimestre_id))
            ->latest('genere_le')
            ->paginate(20)
            ->withQueryString();

        return view('bulletins.index', [
            'classes' => $classes,
            'trimestres' => Trimestre::orderByDesc('date_debut')->get(),
            'trimestreActuel' => Trimestre::actuel(),
            'bulletins' => $bulletins,
        ]);
    }

    /**
     * Un admin voit toutes les classes ; un enseignant ne voit que celles
     * dont il est titulaire (seul le prof titulaire gère les bulletins de sa classe).
     */
    private function classesAutorisees()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return Classe::orderBy('nom')->get();
        }

        return Classe::whereIn('id', $user->enseignant?->classesPrincipales()->pluck('id') ?? [])->orderBy('nom')->get();
    }

    public function genererClasse(Classe $classe, Trimestre $trimestre): RedirectResponse
    {
        $this->authorize('create', [Bulletin::class, $classe]);

        $elevesAvecMoyenne = $classe->elevesActifs()->get()->map(function (Eleve $eleve) use ($trimestre) {
            return ['eleve' => $eleve, 'moyenne' => $eleve->bulletinDonnees($trimestre->id)['moyenne_generale']];
        })->sortByDesc('moyenne')->values();

        foreach ($elevesAvecMoyenne as $rang => $entry) {
            $this->genererBulletin($entry['eleve'], $trimestre, $rang + 1);
        }

        return back()->with('success', "Bulletins générés pour {$elevesAvecMoyenne->count()} élève(s) de {$classe->nom}.");
    }

    public function show(Eleve $eleve, Trimestre $trimestre): StreamedResponse
    {
        $bulletin = Bulletin::where('eleve_id', $eleve->id)->where('trimestre_id', $trimestre->id)->first();

        $this->authorize('view', $bulletin ?? new Bulletin(['eleve_id' => $eleve->id]));

        if (! $bulletin || ! $bulletin->chemin_pdf || ! Storage::disk('local')->exists($bulletin->chemin_pdf)) {
            $bulletin = $this->genererBulletin($eleve, $trimestre, $this->calculerRang($eleve, $trimestre));
        }

        return Storage::disk('local')->response($bulletin->chemin_pdf, "bulletin-{$eleve->matricule}-{$trimestre->nom}.pdf");
    }

    private function calculerRang(Eleve $eleve, Trimestre $trimestre): ?int
    {
        if (! $eleve->classe_id) {
            return null;
        }

        $moyennes = $eleve->classe->elevesActifs()->get()
            ->map(fn (Eleve $e) => $e->bulletinDonnees($trimestre->id)['moyenne_generale'])
            ->filter()
            ->sortDesc()
            ->values();

        $moyenneEleve = $eleve->bulletinDonnees($trimestre->id)['moyenne_generale'];

        return $moyenneEleve === null ? null : $moyennes->search($moyenneEleve) + 1;
    }

    private function genererBulletin(Eleve $eleve, Trimestre $trimestre, ?int $rang): Bulletin
    {
        $donnees = $eleve->bulletinDonnees($trimestre->id);
        $tauxPresence = $eleve->tauxPresenceTrimestre($trimestre->id);

        $pdf = Pdf::loadView('bulletins.pdf', [
            'eleve' => $eleve,
            'trimestre' => $trimestre,
            'matieres' => $donnees['matieres'],
            'moyenneGenerale' => $donnees['moyenne_generale'],
            'tauxPresence' => $tauxPresence,
            'rang' => $rang,
            'appreciation' => $this->appreciation($donnees['moyenne_generale']),
        ]);

        $chemin = "bulletins/{$eleve->matricule}-{$trimestre->id}.pdf";
        Storage::disk('local')->put($chemin, $pdf->output());

        return Bulletin::updateOrCreate(
            ['eleve_id' => $eleve->id, 'trimestre_id' => $trimestre->id],
            [
                'moyenne_generale' => $donnees['moyenne_generale'],
                'rang' => $rang,
                'appreciation' => $this->appreciation($donnees['moyenne_generale']),
                'chemin_pdf' => $chemin,
                'genere_le' => now(),
            ]
        );
    }

    private function appreciation(?float $moyenne): string
    {
        return match (true) {
            $moyenne === null => 'Aucune note',
            $moyenne >= 16 => 'Excellent',
            $moyenne >= 14 => 'Très bien',
            $moyenne >= 12 => 'Bien',
            $moyenne >= 10 => 'Passable',
            default => 'Insuffisant',
        };
    }
}
