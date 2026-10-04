<?php

namespace App\Services;

use App\Models\Classe;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Détermine qui peut échanger avec qui : un enseignant ne peut contacter que les
 * parents des élèves de ses classes, et réciproquement — pas d'accès libre à
 * l'ensemble des comptes de l'école.
 */
class MessagerieService
{
    public function interlocuteursPossibles(User $user): Collection
    {
        if ($user->isEnseignant()) {
            return $this->parentsDesClassesDe($user);
        }

        if ($user->isParent()) {
            return $this->enseignantsDesEnfantsDe($user);
        }

        return collect();
    }

    public function peuventEchanger(User $a, User $b): bool
    {
        return $this->interlocuteursPossibles($a)->contains('id', $b->id);
    }

    private function classesDe(User $user): Collection
    {
        $enseignant = $user->enseignant;

        if (! $enseignant) {
            return collect();
        }

        return $enseignant->classes()->get()
            ->merge($enseignant->classesPrincipales()->get())
            ->unique('id');
    }

    private function parentsDesClassesDe(User $user): Collection
    {
        $eleveIds = $this->classesDe($user)
            ->flatMap(fn (Classe $classe) => $classe->eleves()->pluck('id'))
            ->unique();

        return User::query()
            ->where('role', User::ROLE_PARENT)
            ->whereHas('tuteur.eleves', fn ($q) => $q->whereIn('eleves.id', $eleveIds))
            ->with('tuteur')
            ->orderBy('name')
            ->get();
    }

    private function enseignantsDesEnfantsDe(User $user): Collection
    {
        $tuteur = $user->tuteur;

        if (! $tuteur) {
            return collect();
        }

        $classeIds = $tuteur->eleves()->with('classe')->get()
            ->pluck('classe.id')
            ->filter()
            ->unique();

        $enseignantIds = Classe::query()
            ->whereIn('id', $classeIds)
            ->get()
            ->flatMap(fn (Classe $classe) => $classe->enseignants()->pluck('enseignants.id')
                ->merge($classe->enseignant_principal_id ? [$classe->enseignant_principal_id] : []))
            ->unique();

        return User::query()
            ->where('role', User::ROLE_ENSEIGNANT)
            ->whereHas('enseignant', fn ($q) => $q->whereIn('id', $enseignantIds))
            ->with('enseignant')
            ->orderBy('name')
            ->get();
    }
}
