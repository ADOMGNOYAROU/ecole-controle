<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Paiement;
use App\Models\PaiementCantine;
use App\Rules\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Cantine : chaque jour, la liste des élèves inscrits qui n'ont pas encore payé leur repas.
 * Cocher un élève enregistre son paiement du jour et le retire de la liste ;
 * ceux qui restent à la fin de la journée n'ont pas payé.
 */
class CantineController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Paiement::class);

        $date = $this->dateDemandee($request);
        $classeId = $request->integer('classe_id') ?: null;

        $nonPayes = Eleve::query()
            ->with('classe')
            ->where('statut', Eleve::STATUT_ACTIF)
            ->where('inscrit_cantine', true)
            ->when($classeId, fn ($q) => $q->where('classe_id', $classeId))
            ->whereDoesntHave('paiementsCantine', fn ($q) => $q->whereDate('date', $date))
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $payes = PaiementCantine::query()
            ->with('eleve.classe')
            ->whereDate('date', $date)
            ->when($classeId, fn ($q) => $q->whereHas('eleve', fn ($e) => $e->where('classe_id', $classeId)))
            ->latest()
            ->get();

        return view('cantine.index', [
            'date' => $date,
            'classeId' => $classeId,
            'classes' => $this->classes(),
            'nonPayes' => $nonPayes,
            'payes' => $payes,
            'prix' => Auth::user()->ecole->prix_cantine,
        ]);
    }

    public function payer(Request $request, Eleve $eleve): RedirectResponse
    {
        $this->authorize('viewAny', Paiement::class);

        $date = $this->dateDemandee($request);
        $prix = Auth::user()->ecole->prix_cantine;

        if ($prix === null) {
            return $this->retourListe($request, $date)
                ->with('error', 'Définissez d\'abord le prix de la cantine dans les réglages.');
        }

        PaiementCantine::firstOrCreate(
            ['eleve_id' => $eleve->id, 'date' => $date->toDateString()],
            ['montant' => $prix, 'enregistre_par_id' => Auth::id()],
        );

        return $this->retourListe($request, $date)
            ->with('success', "{$eleve->nomComplet()} : cantine payée.");
    }

    public function annuler(Request $request, PaiementCantine $paiementCantine): RedirectResponse
    {
        $this->authorize('viewAny', Paiement::class);

        $nom = $paiementCantine->eleve->nomComplet();
        $date = $paiementCantine->date;
        $paiementCantine->delete();

        return $this->retourListe($request, $date)
            ->with('success', "{$nom} remis dans la liste des non payés.");
    }

    public function parametres(): View
    {
        $this->authorize('viewAny', Paiement::class);

        $eleves = Eleve::query()
            ->with('classe')
            ->where('statut', Eleve::STATUT_ACTIF)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get()
            ->groupBy(fn (Eleve $eleve) => $eleve->classe?->nom ?? 'Sans classe')
            ->sortKeys();

        return view('cantine.parametres', [
            'prix' => Auth::user()->ecole->prix_cantine,
            'elevesParClasse' => $eleves,
        ]);
    }

    public function enregistrerParametres(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Paiement::class);

        $donnees = $request->validate([
            'prix_cantine' => ['required', 'numeric', 'min:0', 'max:100000'],
            'eleves' => ['nullable', 'array'],
            'eleves.*' => [Tenant::exists('eleves')],
        ]);

        Auth::user()->ecole->update(['prix_cantine' => $donnees['prix_cantine']]);

        $inscrits = $donnees['eleves'] ?? [];
        Eleve::where('statut', Eleve::STATUT_ACTIF)->whereNotIn('id', $inscrits)->update(['inscrit_cantine' => false]);
        Eleve::whereIn('id', $inscrits)->update(['inscrit_cantine' => true]);

        return redirect()->route('cantine.index')
            ->with('success', count($inscrits).' élève(s) inscrit(s) à la cantine.');
    }

    private function dateDemandee(Request $request): Carbon
    {
        $request->validate(['date' => ['nullable', 'date']]);

        return $request->filled('date') ? Carbon::parse($request->input('date'))->startOfDay() : today();
    }

    private function retourListe(Request $request, Carbon $date): RedirectResponse
    {
        return redirect()->route('cantine.index', array_filter([
            'date' => $date->isToday() ? null : $date->toDateString(),
            'classe_id' => $request->input('classe_id'),
        ]));
    }

    private function classes()
    {
        $annee = AnneeScolaire::active();

        return Classe::query()
            ->when($annee, fn ($q) => $q->where('annee_scolaire_id', $annee->id))
            ->orderBy('nom')
            ->get();
    }
}
