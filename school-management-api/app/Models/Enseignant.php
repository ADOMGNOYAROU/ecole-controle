<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enseignant extends Model
{
    use HasFactory, BelongsToEcole;

    protected $fillable = [
        'ecole_id',
        'user_id',
        'nom',
        'prenom',
        'telephone',
        'email',
        'specialite',
        'date_embauche',
        'photo',
    ];

    protected $casts = [
        'date_embauche' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classesPrincipales(): HasMany
    {
        return $this->hasMany(Classe::class, 'enseignant_principal_id');
    }

    public function matieres(): BelongsToMany
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere_classe')
            ->withPivot('classe_id')
            ->withTimestamps();
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classe::class, 'enseignant_matiere_classe')
            ->withPivot('matiere_id')
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

    public function responsabilites(): HasMany
    {
        return $this->hasMany(Responsabilite::class);
    }

    public function creneauxHoraires(): HasMany
    {
        return $this->hasMany(CreneauHoraire::class);
    }

    public function nomComplet(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function estProfTitulaire(): bool
    {
        return $this->classesPrincipales()->exists();
    }
}
