<?php

namespace App\Policies;

use App\Models\Matiere;
use App\Models\User;

class MatierePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Matiere $matiere): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Matiere $matiere): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Matiere $matiere): bool
    {
        return $user->isAdmin();
    }
}
