<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToEcole;
use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class Paiement extends Model
{
    use Auditable, BelongsToEcole, HasFactory;

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
        'dernier_rappel_le',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'dernier_rappel_le' => 'datetime',
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

    /** Numéro de reçu stable : école + paiement (ex. REC-0003-000127). */
    public function numeroRecu(): string
    {
        return sprintf('REC-%04d-%06d', $this->ecole_id, $this->id);
    }

    /** Lien signé vers le reçu PDF, consultable sans compte (ex. depuis WhatsApp). */
    public function lienRecuPublic(): string
    {
        return URL::signedRoute('recus.public', ['paiement' => $this->id]);
    }

    /** Lien WhatsApp vers un tuteur, avec un message de confirmation déjà rédigé. */
    public function lienWhatsApp(Tuteur $tuteur): string
    {
        $eleve = $this->eleve;
        $ecole = $this->ecole;
        $f = fn ($montant) => number_format((float) $montant, 0, ',', ' ').' F';

        $message = "Bonjour {$tuteur->prenom} {$tuteur->nom}, "
            ."{$ecole->nom} confirme le paiement de {$f($this->montant_paye)} ({$this->type}) "
            ."pour {$eleve->nomComplet()}".($eleve->classe ? " ({$eleve->classe->nom})" : '').'. '
            .($this->solde() > 0 ? "Reste à payer : {$f($this->solde())}. " : 'Le montant est entièrement réglé. ')
            ."Votre reçu {$this->numeroRecu()} : {$this->lienRecuPublic()}";

        return WhatsApp::lien($tuteur->telephone, $message);
    }
}
