<?php

namespace App\Policies;

use App\Models\Tuteur;
use App\Models\User;

class TuteurPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Tuteur $tuteur): bool
    {
        return $user->isAdmin() || $user->tuteur?->id === $tuteur->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Tuteur $tuteur): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Tuteur $tuteur): bool
    {
        return $user->isAdmin();
    }
}
