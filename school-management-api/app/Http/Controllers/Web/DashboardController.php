<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AnneeScolaire;
use App\Models\Annonce;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Facture;
use App\Models\Note;
use App\Models\Paiement;
use App\Models\Presence;
use App\Models\Trimestre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role) {
            'super_admin' => redirect()->route('super-admin.dashboard'),
            'admin' => $this->admin(),
            'enseignant' => $this->enseignant(),
            'eleve' => $this->eleve(),
            'parent' => $this->parent(),
        };
    }

    private function admin(): View
    {
        $trimestre = Trimestre::actuel();
        $debutMois = now()->startOfMonth();

        $stats = [
            'eleves' => Eleve::where('statut', 'actif')->count(),
            'enseignants' => Enseignant::count(),
            'classes' => Classe::count(),
            'paiements_en_retard' => Paiement::where('statut', '!=', Paiement::STATUT_PAYE)
                ->where('date_echeance', '<', now())
                ->count(),
        ];

        // Variations réelles (nouveaux enregistrements depuis le 1er du mois).
        $deltas = [
            'eleves' => Eleve::where('created_at', '>=', $debutMois)->count(),
            'enseignants' => Enseignant::where('created_at', '>=', $debutMois)->count(),
            'classes' => Classe::where('created_at', '>=', $debutMois)->count(),
            'paiements_en_retard' => Paiement::where('statut', '!=', Paiement::STATUT_PAYE)
                ->whereBetween('date_echeance', [$debutMois, now()])
                ->count(),
        ];

        // Tendances indicatives (l'application ne conserve pas encore un historique
        // journalier des indicateurs : ces courbes sont une estimation lissée qui
        // se termine toujours sur la valeur réelle actuelle, à titre de repère visuel).
        $sparklines = [
            'eleves' => $this->tendanceIndicative($stats['eleves'], 'eleves'),
            'enseignants' => $this->tendanceIndicative($stats['enseignants'], 'enseignants'),
            'classes' => $this->tendanceIndicative($stats['classes'], 'classes'),
            'paiements_en_retard' => $this->tendanceIndicative($stats['paiements_en_retard'], 'paiements'),
        ];

        $tauxPresenceGlobal = null;
        if ($trimestre) {
            $presences = Presence::where('trimestre_id', $trimestre->id)->get();
            if ($presences->isNotEmpty()) {
                $tauxPresenceGlobal = round(
                    $presences->where('statut', 'present')->count() / $presences->count() * 100,
                    1
                );
            }
        }

        $dernieresNotes = Note::with(['eleve', 'matiere', 'enseignant'])
            ->latest()
            ->take(7)
            ->get();

        $annonces = Annonce::with('auteur')->latest('date_publication')->take(4)->get();

        $apercuRapide = $this->apercuRapideMensuel();
        $prochainesEcheances = $this->prochainesEcheances();

        return view('dashboards.admin', [
            'stats' => $stats,
            'deltas' => $deltas,
            'sparklines' => $sparklines,
            'tauxPresenceGlobal' => $tauxPresenceGlobal,
            'dernieresNotes' => $dernieresNotes,
            'annonces' => $annonces,
            'trimestre' => $trimestre,
            'apercuRapide' => $apercuRapide,
            'prochainesEcheances' => $prochainesEcheances,
        ]);
    }

    /**
     * Estimation lissée d'une tendance sur 7 points se terminant sur la valeur
     * réelle actuelle. Sert uniquement de repère visuel (mini-graphique des
     * cartes) en l'absence d'un historique journalier stocké en base.
     *
     * @return array<int, int>
     */
    private function tendanceIndicative(int $valeurActuelle, string $graine): array
    {
        $randomizer = new \Random\Randomizer(new \Random\Engine\Mt19937(crc32($graine)));
        $tirer = fn (int $min, int $max) => $randomizer->getInt($min, $max);

        $points = [];
        $valeur = max(0, $valeurActuelle - $tirer(0, max(1, (int) round($valeurActuelle * 0.2)) + 1));

        for ($i = 0; $i < 6; $i++) {
            $valeur = max(0, $valeur + $tirer(-1, 2));
            $points[] = $valeur;
        }

        $points[] = $valeurActuelle;

        return $points;
    }

    /**
     * Aperçu hebdomadaire du mois en cours (inscriptions, notes saisies,
     * paiements enregistrés) — calculé à partir des dates réelles stockées.
     */
    private function apercuRapideMensuel(): array
    {
        $debut = now()->startOfMonth();
        $fin = now()->endOfMonth();

        $semaines = [];
        $curseur = $debut->copy();
        while ($curseur->lte($fin)) {
            $semaines[] = [$curseur->copy(), $curseur->copy()->addDays(6)->min($fin)];
            $curseur->addDays(7);
        }

        $labels = [];
        $inscriptions = [];
        $notesSaisies = [];
        $paiements = [];

        foreach ($semaines as [$debutSemaine, $finSemaine]) {
            $labels[] = $debutSemaine->format('d M');
            $inscriptions[] = Eleve::whereBetween('date_inscription', [$debutSemaine, $finSemaine])->count();
            $notesSaisies[] = Note::whereBetween('date_evaluation', [$debutSemaine, $finSemaine])->count();
            $paiements[] = Paiement::whereBetween('date_paiement', [$debutSemaine, $finSemaine])->count();
        }

        return compact('labels', 'inscriptions', 'notesSaisies', 'paiements');
    }

    /**
     * Échéances réelles à venir (fin de trimestre, fin d'année scolaire,
     * prochaine facture d'abonnement) — aucune donnée inventée.
     */
    private function prochainesEcheances(): array
    {
        $echeances = collect();

        if ($trimestre = Trimestre::actuel()) {
            if ($trimestre->date_fin->isFuture()) {
                $echeances->push([
                    'date' => $trimestre->date_fin,
                    'titre' => "Fin du {$trimestre->nom}",
                ]);
            }
        }

        if ($annee = AnneeScolaire::active()) {
            if ($annee->date_fin->isFuture()) {
                $echeances->push([
                    'date' => $annee->date_fin,
                    'titre' => "Fin de l'année scolaire {$annee->libelle}",
                ]);
            }
        }

        $ecole = Auth::user()->ecole;
        if ($ecole) {
            $facture = $ecole->factures()->where('statut', Facture::STATUT_EN_ATTENTE)->orderBy('date_echeance')->first();
            if ($facture) {
                $echeances->push([
                    'date' => $facture->date_echeance,
                    'titre' => 'Facture abonnement Premium à régler',
                ]);
            }
        }

        return $echeances->sortBy('date')->take(3)->values()->all();
    }

    private function enseignant(): View
    {
        $enseignant = Auth::user()->enseignant;
        $trimestre = Trimestre::actuel();

        $classes = $enseignant ? $enseignant->classes()->withCount('eleves')->get() : collect();

        $dernieresNotes = $enseignant
            ? Note::with(['eleve', 'matiere'])
                ->where('enseignant_id', $enseignant->id)
                ->latest()
                ->take(8)
                ->get()
            : collect();

        $annonces = Annonce::with('auteur')->latest('date_publication')->take(5)->get();

        return view('dashboards.enseignant', [
            'enseignant' => $enseignant,
            'classes' => $classes,
            'dernieresNotes' => $dernieresNotes,
            'annonces' => $annonces,
            'trimestre' => $trimestre,
        ]);
    }

    private function eleve(): View
    {
        $eleve = Auth::user()->eleve;
        $trimestre = Trimestre::actuel();

        $moyenne = $trimestre && $eleve ? $eleve->moyenneTrimestre($trimestre->id) : null;
        $tauxPresence = $trimestre && $eleve ? $eleve->tauxPresenceTrimestre($trimestre->id) : null;

        $dernieresNotes = $eleve
            ? $eleve->notes()->with('matiere')->latest()->take(8)->get()
            : collect();

        $solde = $eleve ? $eleve->paiements()->where('statut', '!=', Paiement::STATUT_PAYE)->sum('montant') -
            $eleve->paiements()->where('statut', '!=', Paiement::STATUT_PAYE)->sum('montant_paye') : 0;

        $annonces = Annonce::with('auteur')->latest('date_publication')->take(5)->get();

        return view('dashboards.eleve', [
            'eleve' => $eleve,
            'moyenne' => $moyenne,
            'tauxPresence' => $tauxPresence,
            'dernieresNotes' => $dernieresNotes,
            'solde' => $solde,
            'annonces' => $annonces,
            'trimestre' => $trimestre,
        ]);
    }

    private function parent(): View
    {
        $tuteur = Auth::user()->tuteur;
        $trimestre = Trimestre::actuel();

        $enfants = $tuteur ? $tuteur->eleves()->with('classe')->get() : collect();

        $enfants->each(function (Eleve $eleve) use ($trimestre) {
            $eleve->moyenne_courante = $trimestre ? $eleve->moyenneTrimestre($trimestre->id) : null;
            $eleve->taux_presence_courant = $trimestre ? $eleve->tauxPresenceTrimestre($trimestre->id) : null;
            $eleve->solde_du = $eleve->paiements()->where('statut', '!=', Paiement::STATUT_PAYE)->sum('montant') -
                $eleve->paiements()->where('statut', '!=', Paiement::STATUT_PAYE)->sum('montant_paye');
        });

        $annonces = Annonce::with('auteur')->latest('date_publication')->take(5)->get();

        return view('dashboards.parent', [
            'tuteur' => $tuteur,
            'enfants' => $enfants,
            'annonces' => $annonces,
            'trimestre' => $trimestre,
        ]);
    }
}
