<?php

namespace App\Policies;

use App\Models\Presence;
use App\Models\User;

class PresencePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Presence $presence): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEnseignant()) {
            return $user->enseignant?->id === $presence->enseignant_id
                || $user->enseignant?->classes()->where('classes.id', $presence->classe_id)->exists();
        }

        if ($user->isEleve()) {
            return $user->eleve?->id === $presence->eleve_id;
        }

        if ($user->isParent()) {
            return $user->tuteur?->eleves()->where('eleves.id', $presence->eleve_id)->exists() ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEnseignant();
    }

    public function update(User $user, Presence $presence): bool
    {
        return $user->isAdmin() || $user->enseignant?->id === $presence->enseignant_id;
    }

    public function delete(User $user, Presence $presence): bool
    {
        return $user->isAdmin() || $user->enseignant?->id === $presence->enseignant_id;
    }
}
