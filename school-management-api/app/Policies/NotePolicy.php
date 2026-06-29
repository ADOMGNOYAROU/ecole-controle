<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Note $note): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEnseignant()) {
            return $user->enseignant?->id === $note->enseignant_id;
        }

        if ($user->isEleve()) {
            return $user->eleve?->id === $note->eleve_id;
        }

        if ($user->isParent()) {
            return $user->tuteur?->eleves()->where('eleves.id', $note->eleve_id)->exists() ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEnseignant();
    }

    public function update(User $user, Note $note): bool
    {
        return $user->isAdmin() || $user->enseignant?->id === $note->enseignant_id;
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->isAdmin() || $user->enseignant?->id === $note->enseignant_id;
    }
}
