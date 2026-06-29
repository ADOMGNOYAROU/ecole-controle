<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory, BelongsToEcole;

    protected $fillable = [
        'ecole_id',
        'eleve_id',
        'matiere_id',
        'enseignant_id',
        'classe_id',
        'trimestre_id',
        'type',
        'valeur',
        'bareme',
        'coefficient',
        'date_evaluation',
        'commentaire',
    ];

    protected $casts = [
        'valeur' => 'decimal:2',
        'bareme' => 'decimal:2',
        'coefficient' => 'decimal:2',
        'date_evaluation' => 'date',
    ];

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function trimestre(): BelongsTo
    {
        return $this->belongsTo(Trimestre::class);
    }

    public function noteSur20(): float
    {
        return round(($this->valeur / $this->bareme) * 20, 2);
    }
}
