<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model
{
    use HasFactory, BelongsToEcole;

    protected $table = 'classes';

    protected $fillable = [
        'ecole_id',
        'nom',
        'niveau',
        'annee_scolaire_id',
        'enseignant_principal_id',
        'capacite',
    ];

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function enseignantPrincipal(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_principal_id');
    }

    public function eleves(): HasMany
    {
        return $this->hasMany(Eleve::class);
    }

    public function elevesActifs(): HasMany
    {
        return $this->eleves()->where('statut', 'actif');
    }

    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_matiere_classe')
            ->withPivot('matiere_id')
            ->withTimestamps()
            ->distinct();
    }

    public function matieres(): BelongsToMany
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere_classe')
            ->withPivot('enseignant_id')
            ->withTimestamps()
            ->distinct();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class);
    }

    public function creneauxHoraires(): HasMany
    {
        return $this->hasMany(CreneauHoraire::class);
    }

    public function annonces(): HasMany
    {
        return $this->hasMany(Annonce::class);
    }

    public function effectif(): int
    {
        return $this->elevesActifs()->count();
    }
}
