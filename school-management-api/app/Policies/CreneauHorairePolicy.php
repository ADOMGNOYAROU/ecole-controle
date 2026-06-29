<?php

namespace App\Policies;

use App\Models\CreneauHoraire;
use App\Models\User;

class CreneauHorairePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CreneauHoraire $creneauHoraire): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, CreneauHoraire $creneauHoraire): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, CreneauHoraire $creneauHoraire): bool
    {
        return $user->isAdmin();
    }
}
