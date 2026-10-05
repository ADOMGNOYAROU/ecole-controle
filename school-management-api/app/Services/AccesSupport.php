<?php

namespace App\Services;

use App\Models\Ecole;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Accès support tracé : le super administrateur voit une école comme son directeur,
 * en lecture seule, pour une durée limitée, avec un motif obligatoire.
 * Entrée, pages vues et sortie sont écrites dans le journal d'audit.
 */
class AccesSupport
{
    public const DUREE_MINUTES = 30;

    private const CLE = 'acces_support';

    public function __construct(private JournalAuditeur $auditeur) {}

    public function demarrer(Ecole $ecole, User $directeur, User $superAdmin, string $motif): void
    {
        session([self::CLE => [
            'ecole_id' => $ecole->id,
            'ecole_nom' => $ecole->nom,
            'directeur_id' => $directeur->id,
            'super_admin_id' => $superAdmin->id,
            'motif' => $motif,
            'debut' => now()->getTimestamp(),
            'expire' => now()->addMinutes(self::DUREE_MINUTES)->getTimestamp(),
            'pages' => 0,
        ]]);

        $this->auditeur->enregistrer('acces_support', $ecole, [
            'motif' => $motif,
            'vu_comme' => $directeur->name,
            'duree_max_minutes' => self::DUREE_MINUTES,
        ], user: $superAdmin);
    }

    /** @return array<string, mixed>|null */
    public function actif(): ?array
    {
        return session(self::CLE);
    }

    public function expire(): bool
    {
        $acces = $this->actif();

        return $acces !== null && now()->getTimestamp() >= $acces['expire'];
    }

    public function minutesRestantes(): int
    {
        $acces = $this->actif();

        return $acces ? max(0, (int) ceil(($acces['expire'] - now()->getTimestamp()) / 60)) : 0;
    }

    public function pageVue(string $page, User $superAdmin): void
    {
        $acces = $this->actif();
        session()->put(self::CLE.'.pages', $acces['pages'] + 1);

        $this->auditeur->enregistrer('page_support', changements: ['page' => $page], ecoleId: $acces['ecole_id'], libelle: $page, user: $superAdmin);
    }

    public function terminer(string $raison, ?User $superAdmin = null): ?int
    {
        $acces = $this->actif();

        if (! $acces) {
            return null;
        }

        session()->forget(self::CLE);

        $this->auditeur->enregistrer('fin_support', changements: [
            'raison' => $raison,
            'duree_minutes' => (int) ceil((min(now()->getTimestamp(), $acces['expire']) - $acces['debut']) / 60),
            'pages_vues' => $acces['pages'],
        ], ecoleId: $acces['ecole_id'], libelle: $acces['ecole_nom'], user: $superAdmin ?? User::find($acces['super_admin_id']));

        return $acces['ecole_id'];
    }

    public static function debut(array $acces): Carbon
    {
        return Carbon::createFromTimestamp($acces['debut']);
    }
}
