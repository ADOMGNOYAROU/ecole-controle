<?php

namespace App\Policies;

use App\Models\Enseignant;
use App\Models\User;

class EnseignantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Enseignant $enseignant): bool
    {
        return $user->isAdmin() || $user->enseignant?->id === $enseignant->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Enseignant $enseignant): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Enseignant $enseignant): bool
    {
        return $user->isAdmin();
    }
}
