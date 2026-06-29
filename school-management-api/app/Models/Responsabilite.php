<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Responsabilite extends Model
{
    use HasFactory;

    protected $table = 'responsabilites';

    protected $fillable = [
        'enseignant_id',
        'type',
        'description',
        'date_debut',
        'date_fin',
        'statut',
        'classe_id',
        'matiere_id',
        'commentaires'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    // Types de responsabilités
    public const TYPE_COURS = 'cours';
    public const TYPE_SURVEILLANCE = 'surveillance';
    public const TYPE_ACTIVITE = 'activite';
    public const TYPE_COMMISSION = 'commission';
    public const TYPE_AUTRE = 'autre';

    // Statuts
    public const STATUT_ACTIF = 'actif';
    public const STATUT_TERMINE = 'termine';
    public const STATUT_ANNULE = 'annule';

    /**
     * Relation avec l'enseignant
     */
    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    /**
     * Relation avec la classe (si applicable)
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * Relation avec la matière (si applicable)
     */
    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    /**
     * Vérifie si la responsabilité est active
     */
    public function estActive(): bool
    {
        return $this->statut === self::STATUT_ACTIF;
    }

    /**
     * Vérifie si la responsabilité est terminée
     */
    public function estTerminee(): bool
    {
        return $this->statut === self::STATUT_TERMINE;
    }

    /**
     * Vérifie si la responsabilité est annulée
     */
    public function estAnnulee(): bool
    {
        return $this->statut === self::STATUT_ANNULE;
    }

    /**
     * Marquer comme terminé
     */
    public function marquerTerminee(): void
    {
        $this->update(['statut' => self::STATUT_TERMINE]);
    }

    /**
     * Marquer comme annulé
     */
    public function marquerAnnulee(string $raison = null): void
    {
        $this->update([
            'statut' => self::STATUT_ANNULE,
            'commentaires' => $raison ? ($this->commentaires . "\nAnnulé: " . $raison) : $this->commentaires
        ]);
    }
}
