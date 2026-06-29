<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Eleve;
use App\Models\Paiement;
use Illuminate\Database\Seeder;

class PaiementSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeScolaire::active();

        foreach (Eleve::all() as $eleve) {
            $montant = 150000;
            $paye = collect([0, $montant / 2, $montant])->random();

            Paiement::create([
                'ecole_id' => Demo::ecoleId(),
                'eleve_id' => $eleve->id,
                'annee_scolaire_id' => $annee->id,
                'type' => 'scolarite',
                'montant' => $montant,
                'montant_paye' => $paye,
                'date_echeance' => $annee->date_debut->copy()->addMonths(2),
                'date_paiement' => $paye > 0 ? $annee->date_debut->copy()->addMonth() : null,
                'statut' => match (true) {
                    $paye >= $montant => Paiement::STATUT_PAYE,
                    $paye > 0 => Paiement::STATUT_PARTIEL,
                    default => Paiement::STATUT_EN_ATTENTE,
                },
            ]);
        }
    }
}
