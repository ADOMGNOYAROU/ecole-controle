<?php

namespace App\Policies;

use App\Models\AnneeScolaire;
use App\Models\User;

class AnneeScolairePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, AnneeScolaire $anneeScolaire): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AnneeScolaire $anneeScolaire): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, AnneeScolaire $anneeScolaire): bool
    {
        return $user->isAdmin();
    }
}
