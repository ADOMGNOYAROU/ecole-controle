<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SmartEngage;
use Illuminate\Console\Command;

/**
 * Envoie à Smart Engage les directeurs déjà inscrits (les nouveaux partent automatiquement).
 */
class SynchroniserSmartEngage extends Command
{
    protected $signature = 'smart-engage:synchroniser';

    protected $description = 'Envoie les emails des directeurs d\'école à Smart Engage';

    public function handle(SmartEngage $smartEngage): int
    {
        if (! $smartEngage->actif()) {
            $this->error('Renseignez SMART_ENGAGE_URL et SMART_ENGAGE_CLE avant de synchroniser.');

            return self::FAILURE;
        }

        $envoyes = 0;
        $echecs = 0;

        User::where('role', User::ROLE_ADMIN)->orderBy('id')->each(function (User $directeur) use ($smartEngage, &$envoyes, &$echecs) {
            $smartEngage->ajouterContact($directeur->email) ? $envoyes++ : $echecs++;
        });

        $this->info("{$envoyes} contact(s) envoyé(s) à Smart Engage, {$echecs} échec(s).");

        return $echecs === 0 ? self::SUCCESS : self::FAILURE;
    }
}
