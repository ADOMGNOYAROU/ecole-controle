<?php

namespace App\Services;

use App\Models\Bulletin;
use App\Models\Eleve;
use App\Models\Note;
use App\Models\Trimestre;
use Illuminate\Support\Collection;

class ProgressionService
{
    public const SEUIL_RISQUE = 10.0;

    public function analyser(Eleve $eleve): array
    {
        $trimestreIds = Note::where('eleve_id', $eleve->id)->distinct()->pluck('trimestre_id');

        $trimestres = Trimestre::whereIn('id', $trimestreIds)
            ->with('anneeScolaire')
            ->orderBy('annee_scolaire_id')
            ->orderBy('ordre')
            ->get();

        $moyennesParTrimestre = $this->moyennesParTrimestre($eleve, $trimestres);
        $moyennesParMatiere   = $this->moyennesParMatiere($eleve);
        $tauxPresence         = $this->tauxPresenceParTrimestre($eleve, $trimestres);

        $pointsForts   = $moyennesParMatiere->sortByDesc('moyenne')->take(3)->values();
        $pointsFaibles = $moyennesParMatiere->sortBy('moyenne')->take(3)->values();

        $tendance = $this->calculerTendance($moyennesParTrimestre);
        $risque   = $this->evaluerRisque($moyennesParTrimestre);

        return compact(
            'trimestres',
            'moyennesParTrimestre',
            'moyennesParMatiere',
            'pointsForts',
            'pointsFaibles',
            'tendance',
            'risque',
            'tauxPresence',
        );
    }

    private function moyennesParTrimestre(Eleve $eleve, Collection $trimestres): Collection
    {
        $bulletins = Bulletin::where('eleve_id', $eleve->id)
            ->whereIn('trimestre_id', $trimestres->pluck('id'))
            ->get()
            ->keyBy('trimestre_id');

        return $trimestres->map(function (Trimestre $t) use ($eleve, $bulletins) {
            $moyenne = isset($bulletins[$t->id])
                ? (float) $bulletins[$t->id]->moyenne_generale
                : $eleve->moyenneTrimestre($t->id);

            return [
                'trimestre' => $t,
                'label'     => $t->nom,
                'moyenne'   => $moyenne,
            ];
        })->filter(fn ($row) => $row['moyenne'] !== null)->values();
    }

    private function tauxPresenceParTrimestre(Eleve $eleve, Collection $trimestres): Collection
    {
        return $trimestres->map(function (Trimestre $t) use ($eleve) {
            return [
                'trimestre'     => $t,
                'taux_presence' => $eleve->tauxPresenceTrimestre($t->id),
            ];
        })->filter(fn ($row) => $row['taux_presence'] !== null)->values();
    }

    private function moyennesParMatiere(Eleve $eleve): Collection
    {
        return $eleve->notes()
            ->with('matiere')
            ->get()
            ->groupBy('matiere_id')
            ->map(function (Collection $notesMatiere) {
                $matiere      = $notesMatiere->first()->matiere;
                $totalPondere = $notesMatiere->sum(fn ($n) => $n->noteSur20() * (float) $n->coefficient);
                $totalCoeff   = $notesMatiere->sum(fn ($n) => (float) $n->coefficient);

                return [
                    'matiere'      => $matiere,
                    'moyenne'      => $totalCoeff > 0 ? round($totalPondere / $totalCoeff, 2) : null,
                    'nombre_notes' => $notesMatiere->count(),
                ];
            })
            ->filter(fn ($m) => $m['moyenne'] !== null)
            ->values();
    }

    private function calculerTendance(Collection $moyennes): string
    {
        if ($moyennes->count() < 2) {
            return 'stable';
        }

        $last         = (float) $moyennes->last()['moyenne'];
        $avantDernier = (float) $moyennes->slice(-2, 1)->first()['moyenne'];
        $diff = $last - $avantDernier;

        if ($diff > 0.5) return 'hausse';
        if ($diff < -0.5) return 'baisse';

        return 'stable';
    }

    private function evaluerRisque(Collection $moyennes): array
    {
        $seuil          = self::SEUIL_RISQUE;
        $consecutifs    = 0;
        $maxConsecutifs = 0;

        foreach ($moyennes as $row) {
            if ((float) $row['moyenne'] < $seuil) {
                $consecutifs++;
                $maxConsecutifs = max($maxConsecutifs, $consecutifs);
            } else {
                $consecutifs = 0;
            }
        }

        $derniereMoyenne = $moyennes->isNotEmpty() ? (float) $moyennes->last()['moyenne'] : null;
        $enRisque = $maxConsecutifs >= 2 || ($derniereMoyenne !== null && $derniereMoyenne < $seuil);

        $niveau = 'faible';
        if ($maxConsecutifs >= 3 || ($derniereMoyenne !== null && $derniereMoyenne < 7.0)) {
            $niveau = 'eleve';
        } elseif ($maxConsecutifs >= 2 || ($derniereMoyenne !== null && $derniereMoyenne < $seuil)) {
            $niveau = 'moyen';
        }

        return [
            'en_risque'             => $enRisque,
            'niveau'                => $niveau,
            'trimestres_sous_seuil' => $maxConsecutifs,
            'seuil'                 => $seuil,
            'derniere_moyenne'      => $derniereMoyenne,
        ];
    }

    /**
     * IDs des élèves en risque basés sur les bulletins (pour affichage en liste).
     * Combine : dernier trimestre < seuil OU 2 derniers trimestres consécutifs < seuil.
     */
    public static function idsElevesARisque(): Collection
    {
        $trimestre = Trimestre::actuel();
        if (! $trimestre) {
            return collect();
        }

        $sousSeuil = Bulletin::where('trimestre_id', $trimestre->id)
            ->where('moyenne_generale', '<', self::SEUIL_RISQUE)
            ->pluck('eleve_id');

        $trimestrePrecedent = Trimestre::where('annee_scolaire_id', $trimestre->annee_scolaire_id)
            ->where('ordre', $trimestre->ordre - 1)
            ->first();

        if ($trimestrePrecedent) {
            $deuxiemeFois = Bulletin::where('trimestre_id', $trimestrePrecedent->id)
                ->where('moyenne_generale', '<', self::SEUIL_RISQUE)
                ->whereIn('eleve_id', $sousSeuil)
                ->pluck('eleve_id');

            return $sousSeuil->merge($deuxiemeFois)->unique()->values();
        }

        return $sousSeuil;
    }
}
