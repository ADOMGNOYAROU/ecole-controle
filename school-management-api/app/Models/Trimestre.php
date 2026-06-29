<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trimestre extends Model
{
    use HasFactory, BelongsToEcole;

    protected $fillable = [
        'ecole_id',
        'annee_scolaire_id',
        'nom',
        'ordre',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class);
    }

    public function bulletins(): HasMany
    {
        return $this->hasMany(Bulletin::class);
    }

    public static function actuel(): ?self
    {
        $anneeActive = AnneeScolaire::active();

        if (! $anneeActive) {
            return null;
        }

        $query = static::where('annee_scolaire_id', $anneeActive->id);

        return $query->clone()
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->first()
            ?? $query->clone()->orderByDesc('ordre')->first();
    }
}
