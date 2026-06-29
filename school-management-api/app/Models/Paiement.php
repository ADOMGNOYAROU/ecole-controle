<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory, BelongsToEcole;

    protected $fillable = [
        'ecole_id',
        'eleve_id',
        'annee_scolaire_id',
        'type',
        'montant',
        'montant_paye',
        'date_echeance',
        'date_paiement',
        'statut',
        'commentaire',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
    ];

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_PARTIEL = 'partiel';
    public const STATUT_PAYE = 'paye';
    public const STATUT_RETARD = 'retard';

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function solde(): float
    {
        return (float) $this->montant - (float) $this->montant_paye;
    }

    public function estEnRetard(): bool
    {
        return $this->statut !== self::STATUT_PAYE && $this->date_echeance->isPast();
    }
}
