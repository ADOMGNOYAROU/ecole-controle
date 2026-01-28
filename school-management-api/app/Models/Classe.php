<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'nom',
        'niveau',
        'effectif_max',
        'annee_scolaire',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATION : Une classe a plusieurs élèves
     */
    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }

    /**
     * RELATION : Une classe a plusieurs enseignants
     * (via la table de liaison enseignant_matiere_classe)
     */
    public function enseignants()
    {
        return $this->belongsToMany(
            Enseignant::class,
            'enseignant_matiere_classe',
            'classe_id',
            'enseignant_id'
        )->withPivot('matiere_id')->withTimestamps();
    }

    /**
     * RELATION : Une classe a plusieurs présences
     */
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * RELATION : Une classe a plusieurs notes
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Calculer l'effectif actuel
     */
    public function getEffectifActuelAttribute()
    {
        return $this->eleves()->where('statut', 'actif')->count();
    }

    /**
     * Vérifier si la classe est pleine
     */
    public function isPleine()
    {
        return $this->effectif_actuel >= $this->effectif_max;
    }
}