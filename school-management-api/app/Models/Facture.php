<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facture extends Model
{
    use HasFactory;

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_PAYEE = 'payee';
    public const STATUT_EN_RETARD = 'en_retard';
    public const STATUT_ANNULEE = 'annulee';

    protected $fillable = [
        'ecole_id',
        'abonnement_id',
        'montant',
        'date_echeance',
        'statut',
        'methode_paiement',
        'reference_transaction',
        'payee_le',
        'confirmee_par_id',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_echeance' => 'date',
        'payee_le' => 'datetime',
    ];

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class);
    }

    public function abonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class);
    }

    public function confirmeePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmee_par_id');
    }

    /**
     * Marque la facture comme payée. Pour l'instant déclenché manuellement par un
     * super-admin ; quand l'API de paiement sera branchée, ce sera appelé par le
     * webhook de confirmation au lieu de l'action manuelle.
     */
    public function confirmerPaiement(User $confirmePar, ?string $methodePaiement = null, ?string $reference = null): void
    {
        $this->update([
            'statut' => self::STATUT_PAYEE,
            'payee_le' => now(),
            'confirmee_par_id' => $confirmePar->id,
            'methode_paiement' => $methodePaiement ?? $this->methode_paiement,
            'reference_transaction' => $reference ?? $this->reference_transaction,
        ]);

        $abonnement = $this->abonnement;

        if (! $abonnement) {
            $dateDebut = now();
            $dateFin = now()->addMonths(3);

            $abonnement = Abonnement::create([
                'ecole_id' => $this->ecole_id,
                'plan' => Ecole::PLAN_PREMIUM,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'statut' => 'actif',
                'montant' => $this->montant,
            ]);

            $this->update(['abonnement_id' => $abonnement->id]);
        } else {
            $abonnement->update([
                'statut' => 'actif',
                'date_debut' => now(),
                'date_fin' => now()->addMonths(3),
            ]);
        }

        $this->ecole->update(['plan' => Ecole::PLAN_PREMIUM, 'statut' => Ecole::STATUT_ACTIF]);
    }
}
