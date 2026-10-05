<?php

namespace App\Models\Concerns;

use App\Services\JournalAuditeur;

/**
 * Enregistre dans le journal d'audit chaque création, modification et suppression du modèle.
 * Un modèle très volumineux (notes, présences) peut ignorer certaines actions en déclarant
 * la constante AUDIT_IGNORE, par exemple ['creation'].
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        foreach (['created' => 'creation', 'updated' => 'modification', 'deleted' => 'suppression'] as $evenement => $action) {
            static::$evenement(function ($modele) use ($action) {
                $ignorees = defined(static::class.'::AUDIT_IGNORE') ? constant(static::class.'::AUDIT_IGNORE') : [];

                if (! in_array($action, $ignorees, true)) {
                    app(JournalAuditeur::class)->modele($action, $modele);
                }
            });
        }
    }
}
