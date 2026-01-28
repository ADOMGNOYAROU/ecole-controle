<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;

    protected $table = 'enseignants';

    protected $fillable = [
        'user_id',
        'matricule',
        'specialite',
        'date_embauche',
        'statut',
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATION : Un enseignant appartient à un user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RELATION : Un enseignant enseigne dans plusieurs classes
     * (via la table de liaison enseignant_matiere_classe)
     */
    public function classes()
    {
        return $this->belongsToMany(
            Classe::class,
            'enseignant_matiere_classe',
            'enseignant_id',
            'classe_id'
        )->withPivot('matiere_id')->withTimestamps();
    }

    /**
     * RELATION : Un enseignant enseigne plusieurs matières
     */
    public function matieres()
    {
        return $this->belongsToMany(
            Matiere::class,
            'enseignant_matiere_classe',
            'enseignant_id',
            'matiere_id'
        )->withPivot('classe_id')->withTimestamps();
    }

    /**
     * RELATION : Un enseignant peut marquer plusieurs présences
     */
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * RELATION : Un enseignant peut saisir plusieurs notes
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * RELATION : Un enseignant peut avoir plusieurs responsabilités
     */
    public function responsabilites()
    {
        return $this->hasMany(Responsabilite::class);
    }
}