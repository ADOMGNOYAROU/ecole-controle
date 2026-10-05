<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Services\PassageAnneeService;
use App\Support\NiveauxScolaires;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Assistant de passage de fin d'année : une seule page pour préparer l'année suivante,
 * avec des propositions (classe suivante, redoublement si moyenne < 10) que l'école valide.
 */
class PassageAnneeController extends Controller
{
    public const SEUIL_REDOUBLEMENT = 10;

    public function __construct(private readonly PassageAnneeService $passage) {}

    public function show(): View|RedirectResponse
    {
        $this->authorize('create', AnneeScolaire::class);

        $source = AnneeScolaire::active();

        if (! $source) {
            return redirect()->route('annees-scolaires.index')
                ->with('error', 'Activez d\'abord l\'année scolaire qui se termine.');
        }

        $classes = Classe::where('annee_scolaire_id', $source->id)
            ->with(['eleves' => fn ($q) => $q->where('statut', Eleve::STATUT_ACTIF)->orderBy('nom')->orderBy('prenom')])
            ->orderBy('nom')
            ->get();

        return view('passage-annee.show', [
            'source' => $source,
            'classes' => $classes,
            'moyennes' => $this->passage->moyennesAnnuelles($source),
            'seuil' => self::SEUIL_REDOUBLEMENT,
            'libelleSuivant' => $this->passage->libelleSuivant($source),
            'debutSuivant' => $source->date_debut->copy()->addYear()->toDateString(),
            'finSuivante' => $source->date_fin->copy()->addYear()->toDateString(),
            'suggestion' => fn (Classe $classe) => NiveauxScolaires::classeSuivante($classe->nom),
        ]);
    }

    public function executer(Request $request): RedirectResponse
    {
        $this->authorize('create', AnneeScolaire::class);

        $source = AnneeScolaire::active();
        abort_unless($source, 404);

        $donnees = $request->validate([
            'annee.libelle' => ['required', 'string', 'max:20', Rule::notIn([$source->libelle])],
            'annee.date_debut' => ['required', 'date'],
            'annee.date_fin' => ['required', 'date', 'after:annee.date_debut'],
            'activer' => ['nullable', 'boolean'],
            'classes' => ['nullable', 'array'],
            'classes.*.destination' => ['nullable', 'string', 'max:100'],
            'classes.*.fin_cycle' => ['nullable', 'boolean'],
            'decisions' => ['nullable', 'array'],
            'decisions.*' => [Rule::in([PassageAnneeService::PASSE, PassageAnneeService::REDOUBLE, PassageAnneeService::QUITTE])],
        ], [
            'annee.libelle.not_in' => 'La nouvelle année doit être différente de l\'année en cours.',
        ]);

        try {
            $bilan = $this->passage->executer(
                $source,
                $donnees['annee'],
                $donnees['classes'] ?? [],
                $donnees['decisions'] ?? [],
                $request->boolean('activer'),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('annees-scolaires.index')->with('success', sprintf(
            'Passage vers %s terminé : %d élève(s) passent, %d redoublent, %d quittent l\'école. Pensez à créer les trimestres de la nouvelle année.',
            $bilan['annee']->libelle,
            $bilan['passes'],
            $bilan['redoublants'],
            $bilan['sortants'],
        ));
    }
}
