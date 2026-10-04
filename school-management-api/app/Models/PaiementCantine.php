<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Paiement de la cantine d'un élève pour une journée. Le montant est figé au moment
 * de l'encaissement : changer le prix de la cantine ne modifie pas l'historique.
 */
class PaiementCantine extends Model
{
    use BelongsToEcole;

    protected $table = 'paiements_cantine';

    protected $fillable = [
        'ecole_id',
        'eleve_id',
        'date',
        'montant',
        'enregistre_par_id',
    ];

    protected $casts = [
        'date' => 'date',
        'montant' => 'decimal:2',
    ];

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par_id');
    }
}
