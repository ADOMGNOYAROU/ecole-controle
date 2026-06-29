<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Annonce extends Model
{
    use HasFactory, BelongsToEcole;

    protected $fillable = [
        'ecole_id',
        'auteur_id',
        'titre',
        'contenu',
        'cible',
        'classe_id',
        'date_publication',
    ];

    protected $casts = [
        'date_publication' => 'datetime',
    ];

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }
}
