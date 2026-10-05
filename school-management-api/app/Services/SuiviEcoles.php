<?php

namespace App\Services;

use App\Models\Ecole;
use App\Models\Facture;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Indicateurs du super administrateur : santé de chaque école, revenus, conversion.
 * Les comptages passent par le query builder, groupés par école, pour rester en
 * quelques requêtes quel que soit le nombre d'écoles.
 */
class SuiviEcoles
{
    public const JOURS_SANS_CONNEXION = 14;

    /**
     * Note de santé sur 100 : cinq critères à 20 points, mesurés sur les 30 derniers jours.
     *
     * @return Collection<int, array{score: int, niveau: string, derniere_connexion: ?Carbon, eleves: int, notes: int, presences: int, paiements: int}>
     */
    public function sante(): Collection
    {
        $depuis = now()->subDays(30);

        $connexions = DB::table('users')->whereNotNull('ecole_id')->groupBy('ecole_id')
            ->pluck(DB::raw('max(derniere_connexion_le) as derniere'), 'ecole_id');
        $eleves = $this->compter('eleves');
        $notes = $this->compter('notes', $depuis);
        $presences = $this->compter('presences', $depuis);
        $paiements = DB::table('paiements')
            ->where(fn ($q) => $q->where('created_at', '>=', $depuis)->orWhere('date_paiement', '>=', $depuis->toDateString()))
            ->groupBy('ecole_id')->pluck(DB::raw('count(*) as total'), 'ecole_id');

        return Ecole::pluck('id')->mapWithKeys(function (int $id) use ($connexions, $eleves, $notes, $presences, $paiements) {
            $derniere = isset($connexions[$id]) ? Carbon::parse($connexions[$id]) : null;
            $nbEleves = (int) ($eleves[$id] ?? 0);

            $score = match (true) {
                $derniere?->gte(now()->subDays(7)) => 20,
                $derniere?->gte(now()->subDays(30)) => 10,
                default => 0,
            };
            $score += $nbEleves >= 20 ? 20 : ($nbEleves > 0 ? 10 : 0);
            $score += ($notes[$id] ?? 0) > 0 ? 20 : 0;
            $score += ($presences[$id] ?? 0) > 0 ? 20 : 0;
            $score += ($paiements[$id] ?? 0) > 0 ? 20 : 0;

            return [$id => [
                'score' => $score,
                'niveau' => $score >= 70 ? 'bonne' : ($score >= 40 ? 'moyenne' : 'faible'),
                'derniere_connexion' => $derniere,
                'eleves' => $nbEleves,
                'notes' => (int) ($notes[$id] ?? 0),
                'presences' => (int) ($presences[$id] ?? 0),
                'paiements' => (int) ($paiements[$id] ?? 0),
            ]];
        });
    }

    /** Essais qui se terminent dans les prochains jours, les plus urgents d'abord. */
    public function essaisQuiFinissent(int $jours = 7): Collection
    {
        return Ecole::where('statut', Ecole::STATUT_ESSAI)
            ->whereBetween('trial_ends_at', [now(), now()->addDays($jours)])
            ->orderBy('trial_ends_at')
            ->get();
    }

    /** Écoles en activité où personne ne s'est connecté depuis JOURS_SANS_CONNEXION jours. */
    public function ecolesEndormies(Collection $sante): Collection
    {
        $limite = now()->subDays(self::JOURS_SANS_CONNEXION);

        return Ecole::whereIn('statut', [Ecole::STATUT_ESSAI, Ecole::STATUT_ACTIF])
            ->where('created_at', '<', $limite)
            ->get()
            ->filter(fn (Ecole $ecole) => ($sante[$ecole->id]['derniere_connexion'] ?? null)?->gte($limite) !== true)
            ->sortBy(fn (Ecole $ecole) => $sante[$ecole->id]['derniere_connexion'] ?? Carbon::create(1970))
            ->values();
    }

    /** @return array<string, float> montant encaissé par mois (clé « 2026-10 »), sur 12 mois, du plus ancien au plus récent */
    public function revenus12Mois(): array
    {
        $debut = now()->startOfMonth()->subMonths(11);

        $mois = [];
        for ($i = 0; $i < 12; $i++) {
            $mois[$debut->copy()->addMonths($i)->format('Y-m')] = 0.0;
        }

        Facture::where('statut', Facture::STATUT_PAYEE)
            ->where('payee_le', '>=', $debut)
            ->get(['montant', 'payee_le'])
            ->each(function (Facture $facture) use (&$mois) {
                $mois[$facture->payee_le->format('Y-m')] += (float) $facture->montant;
            });

        return $mois;
    }

    /** @return array{inscrites: int, payantes: int, taux: int, essais_perdus: int} */
    public function conversion(): array
    {
        $inscrites = Ecole::count();
        $idsPayantes = Facture::where('statut', Facture::STATUT_PAYEE)->distinct()->pluck('ecole_id');

        return [
            'inscrites' => $inscrites,
            'payantes' => $idsPayantes->count(),
            'taux' => $inscrites > 0 ? (int) round($idsPayantes->count() * 100 / $inscrites) : 0,
            // Essai terminé sans aucun paiement : écoles à relancer ou perdues
            'essais_perdus' => Ecole::where('trial_ends_at', '<', now())
                ->whereNotIn('id', $idsPayantes)
                ->where('statut', '!=', Ecole::STATUT_SUSPENDU)
                ->count(),
        ];
    }

    /** @return array{reussis: int, montant: float, non_finalises: int} paiements en ligne PayDunya du mois en cours */
    public function payDunyaDuMois(): array
    {
        $debutMois = now()->startOfMonth();

        $reussis = Facture::where('statut', Facture::STATUT_PAYEE)
            ->where('methode_paiement', 'paydunya')
            ->where('payee_le', '>=', $debutMois);

        return [
            'reussis' => (clone $reussis)->count(),
            'montant' => (float) $reussis->sum('montant'),
            // Facture PayDunya créée (jeton enregistré) mais jamais payée : abandon ou échec
            'non_finalises' => Facture::whereNotNull('reference_transaction')
                ->whereIn('statut', [Facture::STATUT_EN_ATTENTE, Facture::STATUT_EN_RETARD])
                ->where('updated_at', '>=', $debutMois)
                ->count(),
        ];
    }

    private function compter(string $table, ?Carbon $depuis = null): Collection
    {
        return DB::table($table)
            ->when($depuis, fn ($q) => $q->where('created_at', '>=', $depuis))
            ->groupBy('ecole_id')
            ->pluck(DB::raw('count(*) as total'), 'ecole_id');
    }
}
