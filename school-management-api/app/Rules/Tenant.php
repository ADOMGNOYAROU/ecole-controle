<?php

namespace App\Rules;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 * Règles de validation limitées à l'école de l'utilisateur connecté.
 *
 * Les règles « exists » et « unique » de Laravel interrogent la base directement,
 * sans le filtre EcoleScope : sans ce helper, un admin pourrait référencer une classe
 * d'une autre école, ou être bloqué par un matricule déjà pris dans une autre école.
 */
class Tenant
{
    public static function exists(string $table, string $column = 'id'): Exists
    {
        return self::limiter(Rule::exists($table, $column));
    }

    public static function unique(string $table, string $column): Unique
    {
        return self::limiter(Rule::unique($table, $column));
    }

    private static function limiter(Exists|Unique $rule): Exists|Unique
    {
        $user = Auth::user();

        if ($user && ! $user->isSuperAdmin()) {
            $rule->where('ecole_id', $user->ecole_id);
        }

        return $rule;
    }
}
