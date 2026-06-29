<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tuteur extends Model
{
    use HasFactory, BelongsToEcole;

    protected $fillable = [
        'ecole_id',
        'user_id',
        'nom',
        'prenom',
        'telephone',
        'email',
        'profession',
        'adresse',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eleves(): BelongsToMany
    {
        return $this->belongsToMany(Eleve::class, 'eleve_tuteur')
            ->withPivot('lien_parente', 'contact_principal')
            ->withTimestamps();
    }

    public function nomComplet(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
