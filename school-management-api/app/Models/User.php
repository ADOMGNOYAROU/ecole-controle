<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Colonnes modifiables
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'telephone',
    ];

    /**
     * Colonnes cachées (ne seront jamais renvoyées dans les réponses JSON)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversion automatique des types
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 10+
    ];

    /**
     * RELATION : Un user peut être un enseignant
     */
    public function enseignant()
    {
        return $this->hasOne(Enseignant::class);
    }

    /**
     * RELATION : Un user peut être un élève
     */
    public function eleve()
    {
        return $this->hasOne(Eleve::class);
    }

    /**
     * Vérifie si l'utilisateur est un administrateur
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est un enseignant
     */
    public function isEnseignant(): bool
    {
        return $this->role === 'enseignant';
    }

    /**
     * Vérifie si l'utilisateur est un professeur titulaire de classe
     */
    public function isProfTitulaire(): bool
    {
        return $this->role === 'prof_titulaire';
    }

    /**
     * Vérifie si l'utilisateur est un parent
     */
    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    /**
     * Vérifie si l'utilisateur est un élève
     */
    public function isEleve(): bool
    {
        return $this->role === 'eleve';
    }
}