<?php

namespace App\Console\Commands;

use App\Mail\RappelPaiementMail;
use App\Models\Ecole;
use App\Models\Notification;
use App\Models\Paiement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnvoyerRappelsPaiements extends Command
{
    protected $signature = 'paiements:rappels
        {--jours=3 : Nombre de jours avant échéance à partir duquel envoyer un rappel}';

    protected $description = 'Envoie un rappel (email + notification) aux parents pour les paiements à échéance proche ou en retard, au maximum une fois par 24h';

    public function handle(): int
    {
        $joursAvant = (int) $this->option('jours');
        $envoyes = 0;

        foreach (Ecole::all() as $ecole) {
            if (! $ecole->aAccesPremium()) {
                continue;
            }

            $paiements = Paiement::query()
                ->where('ecole_id', $ecole->id)
                ->where('statut', '!=', Paiement::STATUT_PAYE)
                ->where('date_echeance', '<=', now()->addDays($joursAvant))
                ->where(fn ($q) => $q->whereNull('dernier_rappel_le')->orWhere('dernier_rappel_le', '<=', now()->subDay()))
                ->with(['eleve.tuteurs.user'])
                ->get();

            foreach ($paiements as $paiement) {
                $this->rappelerPourPaiement($paiement);
                $envoyes++;
            }
        }

        $this->info("{$envoyes} rappel(s) de paiement envoyé(s).");

        return self::SUCCESS;
    }

    private function rappelerPourPaiement(Paiement $paiement): void
    {
        $tuteurs = $paiement->eleve->tuteurs;

        foreach ($tuteurs as $tuteur) {
            if (! $tuteur->user) {
                continue;
            }

            if ($tuteur->user->email) {
                Mail::to($tuteur->user->email)->send(new RappelPaiementMail($paiement));
            }

            Notification::create([
                'user_id' => $tuteur->user->id,
                'titre' => $paiement->estEnRetard() ? 'Paiement en retard' : 'Échéance de paiement proche',
                'message' => $paiement->eleve->nomComplet().' — '.number_format($paiement->solde(), 0, ',', ' ').' FCFA dû(s) pour le '.$paiement->date_echeance->format('d/m/Y'),
                'type' => 'paiement_rappel',
                'lien' => route('mes-enfants.show', $paiement->eleve),
            ]);
        }

        $paiement->update(['dernier_rappel_le' => now()]);
    }
}
