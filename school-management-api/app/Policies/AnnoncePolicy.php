<?php

namespace App\Policies;

use App\Models\Annonce;
use App\Models\User;

class AnnoncePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Annonce $annonce): bool
    {
        if ($user->isAdmin() || $annonce->cible === 'tous') {
            return true;
        }

        return match ($annonce->cible) {
            'parents' => $user->isParent(),
            'enseignants' => $user->isEnseignant(),
            'eleves' => $user->isEleve(),
            'classe' => $user->eleve?->classe_id === $annonce->classe_id
                || ($user->isParent() && $user->tuteur?->eleves()->where('classe_id', $annonce->classe_id)->exists()),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEnseignant();
    }

    public function update(User $user, Annonce $annonce): bool
    {
        return $user->isAdmin() || $user->id === $annonce->auteur_id;
    }

    public function delete(User $user, Annonce $annonce): bool
    {
        return $user->isAdmin() || $user->id === $annonce->auteur_id;
    }
}
