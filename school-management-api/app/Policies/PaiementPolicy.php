<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;

class PaiementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Paiement $paiement): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEleve()) {
            return $user->eleve?->id === $paiement->eleve_id;
        }

        if ($user->isParent()) {
            return $user->tuteur?->eleves()->where('eleves.id', $paiement->eleve_id)->exists() ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Paiement $paiement): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Paiement $paiement): bool
    {
        return $user->isAdmin();
    }
}
