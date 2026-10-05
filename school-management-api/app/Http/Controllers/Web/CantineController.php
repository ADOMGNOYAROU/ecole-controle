<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AbonnementCantine;
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
 * Cocher un élève enregistre son paiement du jour (ou du mois entier) et le retire de la liste ;
 * ceux qui restent à la fin de la journée n'ont pas payé.
 */
class CantineController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Paiement::class);

        $date = $this->dateDemandee($request);
        $mois = $date->copy()->startOfMonth();
        $classeId = $request->integer('classe_id') ?: null;
        $dansLaClasse = fn ($q) => $q->whereHas('eleve', fn ($e) => $e->where('classe_id', $classeId));

        $nonPayes = Eleve::query()
            ->with('classe')
            ->where('statut', Eleve::STATUT_ACTIF)
            ->where('inscrit_cantine', true)
            ->when($classeId, fn ($q) => $q->where('classe_id', $classeId))
            ->whereDoesntHave('paiementsCantine', fn ($q) => $q->whereDate('date', $date))
            ->whereDoesntHave('abonnementsCantine', fn ($q) => $q->whereDate('mois', $mois))
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $payes = PaiementCantine::query()
            ->with(['eleve.classe', 'eleve.tuteurs'])
            ->whereDate('date', $date)
            ->when($classeId, $dansLaClasse)
            ->latest()
            ->get();

        $abonnes = AbonnementCantine::query()
            ->with(['eleve.classe', 'eleve.tuteurs'])
            ->whereDate('mois', $mois)
            ->when($classeId, $dansLaClasse)
            ->latest()
            ->get();

        return view('cantine.index', [
            'date' => $date,
            'mois' => $mois,
            'classeId' => $classeId,
            'classes' => $this->classes(),
            'nonPayes' => $nonPayes,
            'payes' => $payes,
            'abonnes' => $abonnes,
            'prix' => Auth::user()->ecole->prix_cantine,
            'prixMois' => Auth::user()->ecole->prix_cantine_mois,
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

    public function payerMois(Request $request, Eleve $eleve): RedirectResponse
    {
        $this->authorize('viewAny', Paiement::class);

        $date = $this->dateDemandee($request);
        $prixMois = Auth::user()->ecole->prix_cantine_mois;

        if ($prixMois === null) {
            return $this->retourListe($request, $date)
                ->with('error', 'Définissez d\'abord le prix du mois de cantine dans les réglages.');
        }

        $abonnement = AbonnementCantine::firstOrCreate(
            ['eleve_id' => $eleve->id, 'mois' => $date->copy()->startOfMonth()->toDateString()],
            ['montant' => $prixMois, 'enregistre_par_id' => Auth::id()],
        );

        return $this->retourListe($request, $date)
            ->with('success', "{$eleve->nomComplet()} : cantine payée pour {$abonnement->libelleMois()}.");
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

    public function annulerMois(Request $request, AbonnementCantine $abonnementCantine): RedirectResponse
    {
        $this->authorize('viewAny', Paiement::class);

        $nom = $abonnementCantine->eleve->nomComplet();
        $abonnementCantine->delete();

        return $this->retourListe($request, $this->dateDemandee($request))
            ->with('success', "{$nom} : paiement du mois annulé.");
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
            'prixMois' => Auth::user()->ecole->prix_cantine_mois,
            'elevesParClasse' => $eleves,
        ]);
    }

    public function enregistrerParametres(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Paiement::class);

        $donnees = $request->validate([
            'prix_cantine' => ['required', 'numeric', 'min:0', 'max:100000'],
            'prix_cantine_mois' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'eleves' => ['nullable', 'array'],
            'eleves.*' => [Tenant::exists('eleves')],
        ]);

        Auth::user()->ecole->update([
            'prix_cantine' => $donnees['prix_cantine'],
            'prix_cantine_mois' => $donnees['prix_cantine_mois'] ?? null,
        ]);

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
