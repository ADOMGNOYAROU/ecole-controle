<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $this->gereLeCompte($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $this->gereLeCompte($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id && $this->gereLeCompte($user, $model);
    }

    /**
     * Un admin ne gère que les comptes de sa propre école, jamais un super_admin.
     */
    private function gereLeCompte(User $user, User $model): bool
    {
        return $user->isAdmin()
            && $user->ecole_id !== null
            && $model->ecole_id === $user->ecole_id
            && ! $model->isSuperAdmin();
    }
}
