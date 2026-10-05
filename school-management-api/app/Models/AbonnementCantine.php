<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEcole;
use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cantine payée pour tout un mois : l'élève sort de la liste de chaque jour de ce mois.
 * « mois » est toujours le premier jour du mois. Le montant est figé à l'encaissement.
 */
class AbonnementCantine extends Model
{
    use BelongsToEcole;

    protected $table = 'abonnements_cantine';

    protected $fillable = [
        'ecole_id',
        'eleve_id',
        'mois',
        'montant',
        'enregistre_par_id',
    ];

    protected $casts = [
        'mois' => 'date',
        'montant' => 'decimal:2',
    ];

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function libelleMois(): string
    {
        return ucfirst($this->mois->locale('fr')->translatedFormat('F Y'));
    }

    public function lienWhatsApp(Tuteur $tuteur): string
    {
        $message = "Bonjour {$tuteur->prenom} {$tuteur->nom}, {$this->ecole->nom} confirme le paiement de la cantine de "
            ."{$this->eleve->nomComplet()} pour tout le mois de {$this->mois->locale('fr')->translatedFormat('F Y')} : "
            .number_format((float) $this->montant, 0, ',', ' ').' F. Merci !';

        return WhatsApp::lien($tuteur->telephone, $message);
    }
}
