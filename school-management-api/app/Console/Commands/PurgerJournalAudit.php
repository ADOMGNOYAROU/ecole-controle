<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Supprime les entrées du journal d'audit plus anciennes que la durée de conservation.
 * Passe par le query builder : le modèle JournalAudit refuse toute suppression.
 */
class PurgerJournalAudit extends Command
{
    public const MOIS_CONSERVATION = 12;

    protected $signature = 'journal:purger';

    protected $description = "Supprime les entrées du journal d'audit de plus de 12 mois";

    public function handle(): int
    {
        $supprimees = DB::table('journal_audit')
            ->where('created_at', '<', now()->subMonths(self::MOIS_CONSERVATION))
            ->delete();

        $this->info("{$supprimees} entrée(s) supprimée(s) du journal d'audit.");

        return self::SUCCESS;
    }
}
