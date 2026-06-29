<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Pas de scope global multi-tenant ici (contrairement aux autres modèles) :
     * la résolution de l'utilisateur authentifié (Auth::user()) interrogerait ce
     * modèle, qui appellerait à son tour Auth::user() pour filtrer le scope —
     * boucle infinie. Le filtrage par école se fait explicitement où nécessaire
     * (ex: UserAccountController::index()).
     */

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ENSEIGNANT = 'enseignant';
    public const ROLE_ELEVE = 'eleve';
    public const ROLE_PARENT = 'parent';

    protected $fillable = [
        'ecole_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_change_password' => 'boolean',
    ];

    public function enseignant(): HasOne
    {
        return $this->hasOne(Enseignant::class);
    }

    public function eleve(): HasOne
    {
        return $this->hasOne(Eleve::class);
    }

    public function tuteur(): HasOne
    {
        return $this->hasOne(Tuteur::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEnseignant(): bool
    {
        return $this->role === self::ROLE_ENSEIGNANT;
    }

    public function isParent(): bool
    {
        return $this->role === self::ROLE_PARENT;
    }

    public function isEleve(): bool
    {
        return $this->role === self::ROLE_ELEVE;
    }
}
