<?php

namespace App\Policies;

use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\User;

class BulletinPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEnseignant()) {
            return $user->enseignant?->estProfTitulaire() ?? false;
        }

        return $user->isEleve() || $user->isParent();
    }

    public function view(User $user, Bulletin $bulletin): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEnseignant()) {
            $classeId = $bulletin->eleve?->classe_id;

            return $classeId !== null && $user->enseignant?->classesPrincipales()->where('id', $classeId)->exists();
        }

        if ($user->isEleve()) {
            return $user->eleve?->id === $bulletin->eleve_id;
        }

        if ($user->isParent()) {
            return $user->tuteur?->eleves()->where('eleves.id', $bulletin->eleve_id)->exists() ?? false;
        }

        return false;
    }

    public function create(User $user, ?Classe $classe = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isEnseignant() || ! $classe) {
            return false;
        }

        return $user->enseignant?->classesPrincipales()->where('id', $classe->id)->exists() ?? false;
    }
}
