<?php

namespace App\Policies;

use App\Models\Eleve;
use App\Models\User;

class ElevePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_ENSEIGNANT]);
    }

    public function view(User $user, Eleve $eleve): bool
    {
        if ($user->isAdmin() || $user->isEnseignant()) {
            return true;
        }

        if ($user->isEleve()) {
            return $user->eleve?->id === $eleve->id;
        }

        if ($user->isParent()) {
            return $user->tuteur?->eleves()->where('eleves.id', $eleve->id)->exists() ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Eleve $eleve): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Eleve $eleve): bool
    {
        return $user->isAdmin();
    }
}
