<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ecole extends Model
{
    use HasFactory;

    public const STATUT_ESSAI = 'essai';
    public const STATUT_ACTIF = 'actif';
    public const STATUT_SUSPENDU = 'suspendu';
    public const STATUT_EXPIRE = 'expire';

    public const PLAN_GRATUIT = 'gratuit';
    public const PLAN_PREMIUM = 'premium';

    public const TARIF_PREMIUM_TRIMESTRIEL = 15000;

    protected $fillable = [
        'nom',
        'slug',
        'email_contact',
        'telephone',
        'adresse',
        'ville',
        'statut',
        'plan',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    public function estEnEssai(): bool
    {
        return $this->statut === self::STATUT_ESSAI
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function abonnementActif(): ?Abonnement
    {
        return $this->abonnements()
            ->where('statut', 'actif')
            ->where('date_fin', '>=', now())
            ->latest('date_fin')
            ->first();
    }

    public function aAccesPremium(): bool
    {
        if ($this->statut === self::STATUT_SUSPENDU) {
            return false;
        }

        if ($this->plan !== self::PLAN_PREMIUM) {
            return false;
        }

        return $this->estEnEssai() || $this->abonnementActif() !== null;
    }

    public function joursEssaiRestants(): int
    {
        if (! $this->estEnEssai()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public static function genererSlug(string $nom): string
    {
        $base = Str::slug($nom);
        $slug = $base;
        $compteur = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$compteur}";
            $compteur++;
        }

        return $slug;
    }
}
