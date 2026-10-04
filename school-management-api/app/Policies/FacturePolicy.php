<?php

namespace App\Policies;

use App\Models\Facture;
use App\Models\User;

class FacturePolicy
{
    public function view(User $user, Facture $facture): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $facture->ecole_id === $user->ecole_id;
        }

        return false;
    }

    public function update(User $user, Facture $facture): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $facture->ecole_id === $user->ecole_id;
        }

        return false;
    }

    public function delete(User $user, Facture $facture): bool
    {
        return $user->isSuperAdmin();
    }
}
