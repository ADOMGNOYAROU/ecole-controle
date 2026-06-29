<?php

namespace App\Policies;

use App\Models\Trimestre;
use App\Models\User;

class TrimestrePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Trimestre $trimestre): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Trimestre $trimestre): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Trimestre $trimestre): bool
    {
        return $user->isAdmin();
    }
}
