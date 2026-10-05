<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToEcole;
use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Paiement de la cantine d'un élève pour une journée. Le montant est figé au moment
 * de l'encaissement : changer le prix de la cantine ne modifie pas l'historique.
 */
class PaiementCantine extends Model
{
    use Auditable, BelongsToEcole;

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

    public function lienWhatsApp(Tuteur $tuteur): string
    {
        $message = "Bonjour {$tuteur->prenom} {$tuteur->nom}, {$this->ecole->nom} confirme le paiement de la cantine de "
            ."{$this->eleve->nomComplet()} pour le {$this->date->format('d/m/Y')} : "
            .number_format((float) $this->montant, 0, ',', ' ').' F. Merci !';

        return WhatsApp::lien($tuteur->telephone, $message);
    }

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par_id');
    }
}
