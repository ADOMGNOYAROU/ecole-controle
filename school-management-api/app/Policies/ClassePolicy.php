<?php

namespace App\Policies;

use App\Models\Classe;
use App\Models\User;

class ClassePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Classe $classe): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEnseignant()) {
            return $user->enseignant?->classes()->where('classes.id', $classe->id)->exists()
                || $classe->enseignant_principal_id === $user->enseignant?->id;
        }

        if ($user->isEleve()) {
            return $user->eleve?->classe_id === $classe->id;
        }

        if ($user->isParent()) {
            return $user->tuteur?->eleves()->where('classe_id', $classe->id)->exists() ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Classe $classe): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Classe $classe): bool
    {
        return $user->isAdmin();
    }
}
