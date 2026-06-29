<?php

namespace App\Models\Concerns;

use App\Models\Ecole;
use App\Models\Scopes\EcoleScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Multi-tenant : filtre automatiquement chaque requête par l'école de l'utilisateur
 * connecté (sauf super_admin, qui voit tout) et assigne ecole_id à la création.
 */
trait BelongsToEcole
{
    public static function bootBelongsToEcole(): void
    {
        static::addGlobalScope(new EcoleScope);

        static::creating(function ($model) {
            if (! $model->ecole_id && Auth::check() && Auth::user()->ecole_id) {
                $model->ecole_id = Auth::user()->ecole_id;
            }
        });
    }

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class);
    }
}
