<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Entrée du journal d'audit. Écrite une fois, jamais modifiée ni supprimée depuis l'application :
 * seule la commande de purge (requête directe) efface les entrées de plus de 12 mois.
 */
class JournalAudit extends Model
{
    public const UPDATED_AT = null;

    public const LIBELLES_ACTIONS = [
        'creation' => 'Création',
        'modification' => 'Modification',
        'suppression' => 'Suppression',
        'connexion' => 'Connexion',
        'connexion_echouee' => 'Connexion échouée',
        'deconnexion' => 'Déconnexion',
        'generation_bulletins' => 'Bulletins générés',
        'passage_annee' => 'Passage de fin d\'année',
        'consultation' => 'Consultation (super admin)',
        'export_journal' => 'Export du journal',
        'prolongation_essai' => 'Essai prolongé',
        'reinitialisation_acces' => 'Accès réinitialisé',
        'note_interne' => 'Note interne',
        'acces_support' => 'Entrée en mode support',
        'page_support' => 'Page vue en mode support',
        'fin_support' => 'Sortie du mode support',
    ];

    protected $table = 'journal_audit';

    protected $fillable = [
        'ecole_id',
        'user_id',
        'user_nom',
        'user_role',
        'action',
        'objet_type',
        'objet_id',
        'objet_libelle',
        'changements',
        'ip',
        'appareil',
    ];

    protected $casts = [
        'changements' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Une entrée du journal d\'audit ne peut pas être modifiée.'));
        static::deleting(fn () => throw new LogicException('Une entrée du journal d\'audit ne peut pas être supprimée.'));
    }

    public function ecole(): BelongsTo
    {
        return $this->belongsTo(Ecole::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function libelleAction(): string
    {
        return self::LIBELLES_ACTIONS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}
