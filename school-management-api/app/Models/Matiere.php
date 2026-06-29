<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matiere extends Model
{
    use HasFactory, BelongsToEcole;

    protected $fillable = [
        'ecole_id',
        'nom',
        'code',
        'coefficient_defaut',
    ];

    protected $casts = [
        'coefficient_defaut' => 'decimal:2',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classe::class, 'enseignant_matiere_classe')
            ->withPivot('enseignant_id')
            ->withTimestamps()
            ->distinct();
    }

    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_matiere_classe')
            ->withPivot('classe_id')
            ->withTimestamps()
            ->distinct();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function creneauxHoraires(): HasMany
    {
        return $this->hasMany(CreneauHoraire::class);
    }
}
