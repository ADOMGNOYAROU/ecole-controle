<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eleve extends Model
{
    use HasFactory;

    protected $table = 'eleves';

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'matricule',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'classe_id',
        'parent_contact',
        'adresse',
        'statut',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELATION : Un élève appartient à une classe
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * RELATION : Un élève peut être lié à un user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RELATION : Un élève peut avoir plusieurs parents
     */
    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'eleve_parent', 'eleve_id', 'parent_id');
    }

    /**
     * RELATION : Un élève a plusieurs présences
     */
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * RELATION : Un élève a plusieurs notes
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Calculer le nom complet
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Calculer l'âge
     */
    public function getAgeAttribute()
    {
        return $this->date_naissance->age;
    }
}