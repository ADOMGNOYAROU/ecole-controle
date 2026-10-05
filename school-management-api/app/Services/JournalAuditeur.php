<?php

namespace App\Services;

use App\Models\Ecole;
use App\Models\JournalAudit;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Écrit les entrées du journal d'audit : qui, quoi, sur quel élément, avant → après, d'où.
 * Une erreur d'écriture du journal ne doit jamais bloquer l'action de l'utilisateur.
 */
class JournalAuditeur
{
    /** Champs jamais recopiés en clair. */
    private const MASQUES = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    /** Champs techniques sans intérêt pour l'audit. */
    private const IGNORES = ['created_at', 'updated_at', 'remember_token', 'email_verified_at', 'dernier_rappel_le', 'derniere_connexion_le'];

    public function enregistrer(
        string $action,
        ?Model $objet = null,
        ?array $changements = null,
        ?int $ecoleId = null,
        ?string $libelle = null,
        ?User $user = null,
    ): ?JournalAudit {
        try {
            $user ??= Auth::user();
            $requete = app()->runningInConsole() ? null : request();

            return JournalAudit::create([
                'ecole_id' => $ecoleId ?? $this->ecoleDe($objet) ?? $user?->ecole_id,
                'user_id' => $user?->id,
                'user_nom' => $user?->name ?? (app()->runningInConsole() ? 'Système' : null),
                'user_role' => $user?->role,
                'action' => $action,
                'objet_type' => $objet ? class_basename($objet) : null,
                'objet_id' => $objet?->getKey(),
                'objet_libelle' => $libelle ?? ($objet ? $this->libelle($objet) : null),
                'changements' => $changements ?: null,
                'ip' => $requete?->ip(),
                'appareil' => $requete ? Str::limit((string) $requete->userAgent(), 250, '') : 'console',
            ]);
        } catch (Throwable $e) {
            Log::error('Journal d\'audit : écriture impossible', ['action' => $action, 'erreur' => $e->getMessage()]);

            return null;
        }
    }

    /** Appelé par le trait Auditable sur création, modification et suppression. */
    public function modele(string $action, Model $modele): void
    {
        $changements = match ($action) {
            'modification' => $this->differences($modele),
            default => $this->instantane($modele),
        };

        if ($action === 'modification' && $changements === []) {
            return;
        }

        $this->enregistrer($action, $modele, $changements);
    }

    /** @return array<string, array{avant: mixed, apres: mixed}> */
    private function differences(Model $modele): array
    {
        $resultat = [];

        foreach (array_keys($modele->getChanges()) as $champ) {
            if (in_array($champ, self::IGNORES, true)) {
                continue;
            }

            $masque = in_array($champ, self::MASQUES, true);
            $resultat[$champ] = [
                'avant' => $masque ? '••••' : $this->valeur($modele->getOriginal($champ)),
                'apres' => $masque ? '••••' : $this->valeur($modele->getAttribute($champ)),
            ];
        }

        return $resultat;
    }

    /** @return array<string, mixed> */
    private function instantane(Model $modele): array
    {
        $resultat = [];

        foreach ($modele->getAttributes() as $champ => $valeur) {
            if (in_array($champ, self::IGNORES, true)) {
                continue;
            }

            $resultat[$champ] = in_array($champ, self::MASQUES, true) ? '••••' : $this->valeur($modele->getAttribute($champ));
        }

        return $resultat;
    }

    private function valeur(mixed $valeur): mixed
    {
        return match (true) {
            $valeur instanceof DateTimeInterface => $valeur->format('Y-m-d H:i:s'),
            is_object($valeur) && method_exists($valeur, '__toString') => (string) $valeur,
            is_object($valeur), is_array($valeur) => json_encode($valeur, JSON_UNESCAPED_UNICODE),
            default => $valeur,
        };
    }

    private function ecoleDe(?Model $objet): ?int
    {
        if ($objet instanceof Ecole) {
            return $objet->getKey();
        }

        $ecoleId = $objet?->getAttribute('ecole_id');

        return $ecoleId === null ? null : (int) $ecoleId;
    }

    private function libelle(Model $objet): string
    {
        return match (true) {
            method_exists($objet, 'nomComplet') => $objet->nomComplet(),
            filled($objet->getAttribute('nom')) => (string) $objet->getAttribute('nom'),
            filled($objet->getAttribute('name')) => (string) $objet->getAttribute('name'),
            filled($objet->getAttribute('libelle')) => (string) $objet->getAttribute('libelle'),
            filled($objet->getAttribute('titre')) => (string) $objet->getAttribute('titre'),
            default => class_basename($objet).' #'.$objet->getKey(),
        };
    }
}
