<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Eleve;
use App\Support\NiveauxScolaires;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Passage de fin d'année : crée (ou reprend) l'année suivante et ses classes, puis place
 * chaque élève selon la décision de l'école. L'année passée, ses classes, notes et
 * bulletins restent intacts et consultables.
 */
class PassageAnneeService
{
    public const PASSE = 'passe';

    public const REDOUBLE = 'redouble';

    public const QUITTE = 'quitte';

    /** Moyenne annuelle par élève, d'après les bulletins générés sur l'année. */
    public function moyennesAnnuelles(AnneeScolaire $annee): Collection
    {
        return Bulletin::query()
            ->whereIn('trimestre_id', $annee->trimestres()->pluck('id'))
            ->whereNotNull('moyenne_generale')
            ->get(['eleve_id', 'moyenne_generale'])
            ->groupBy('eleve_id')
            ->map(fn (Collection $bulletins) => round((float) $bulletins->avg('moyenne_generale'), 2));
    }

    /** « 2026-2027 » devient « 2027-2028 » ; sinon une chaîne vide à compléter. */
    public function libelleSuivant(AnneeScolaire $annee): string
    {
        if (preg_match('/^(\d{4})\s*[-\/]\s*(\d{4})$/', trim($annee->libelle), $m)) {
            return ($m[1] + 1).'-'.($m[2] + 1);
        }

        return '';
    }

    /**
     * @param  array{libelle: string, date_debut: string, date_fin: string}  $nouvelleAnnee
     * @param  array<int, array{destination?: ?string, fin_cycle?: bool}>  $classes  par id de classe source
     * @param  array<int, string>  $decisions  par id d'élève : passe, redouble ou quitte
     * @return array{annee: AnneeScolaire, passes: int, redoublants: int, sortants: int}
     */
    public function executer(AnneeScolaire $source, array $nouvelleAnnee, array $classes, array $decisions, bool $activer): array
    {
        if (trim($nouvelleAnnee['libelle']) === $source->libelle) {
            throw new InvalidArgumentException('La nouvelle année doit être différente de l\'année en cours.');
        }

        return DB::transaction(function () use ($source, $nouvelleAnnee, $classes, $decisions, $activer) {
            $cible = AnneeScolaire::firstOrCreate(
                ['libelle' => trim($nouvelleAnnee['libelle'])],
                ['date_debut' => $nouvelleAnnee['date_debut'], 'date_fin' => $nouvelleAnnee['date_fin'], 'active' => false],
            );

            if ($activer) {
                AnneeScolaire::whereKeyNot($cible->id)->update(['active' => false]);
                $cible->update(['active' => true]);
            }

            $bilan = ['annee' => $cible, 'passes' => 0, 'redoublants' => 0, 'sortants' => 0];

            $classesSource = Classe::where('annee_scolaire_id', $source->id)
                ->with(['eleves' => fn ($q) => $q->where('statut', Eleve::STATUT_ACTIF)])
                ->get();

            foreach ($classesSource as $classe) {
                $reglage = $classes[$classe->id] ?? [];
                $finDeCycle = (bool) ($reglage['fin_cycle'] ?? false);
                $destination = trim((string) ($reglage['destination'] ?? '')) ?: NiveauxScolaires::classeSuivante($classe->nom);
                $finDeCycle = $finDeCycle || $destination === null;

                $classeSuivante = $finDeCycle ? null : $this->classeDansAnnee($cible, $destination, $this->niveauSuivant($classe->niveau));
                $classeRedoublement = null;

                foreach ($classe->eleves as $eleve) {
                    $decision = $decisions[$eleve->id] ?? self::PASSE;

                    if ($decision === self::QUITTE) {
                        $eleve->update(['statut' => Eleve::STATUT_INACTIF]);
                        $bilan['sortants']++;
                    } elseif ($decision === self::REDOUBLE) {
                        $classeRedoublement ??= $this->classeDansAnnee($cible, $classe->nom, $classe->niveau);
                        $eleve->update(['classe_id' => $classeRedoublement->id]);
                        $bilan['redoublants']++;
                    } elseif ($finDeCycle) {
                        $eleve->update(['statut' => Eleve::STATUT_DIPLOME]);
                        $bilan['sortants']++;
                    } else {
                        $eleve->update(['classe_id' => $classeSuivante->id]);
                        $bilan['passes']++;
                    }
                }
            }

            return $bilan;
        });
    }

    private function classeDansAnnee(AnneeScolaire $annee, string $nom, ?string $niveau): Classe
    {
        return Classe::firstOrCreate(
            ['annee_scolaire_id' => $annee->id, 'nom' => $nom],
            ['niveau' => $niveau],
        );
    }

    private function niveauSuivant(?string $niveau): ?string
    {
        return $niveau === null || $niveau === '' ? null : (NiveauxScolaires::classeSuivante($niveau) ?? $niveau);
    }
}
