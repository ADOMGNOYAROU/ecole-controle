<?php

namespace Database\Seeders;

use App\Models\Abonnement;
use App\Models\Ecole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EcoleSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'ecole_id' => null,
            'name' => 'Super Administrateur',
            'email' => 'superadmin@ecole-manager.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $ecole = Ecole::create([
            'nom' => 'École Démo',
            'slug' => Ecole::genererSlug('École Démo'),
            'email_contact' => 'contact@ecole-demo.test',
            'telephone' => '0600000000',
            'ville' => 'Lomé',
            'statut' => Ecole::STATUT_ACTIF,
            'plan' => Ecole::PLAN_PREMIUM,
            'trial_ends_at' => null,
        ]);

        // École Démo doit refléter un abonnement Premium réellement actif (et non
        // seulement les champs statut/plan) pour que aAccesPremium() soit vrai.
        Abonnement::create([
            'ecole_id' => $ecole->id,
            'plan' => Ecole::PLAN_PREMIUM,
            'date_debut' => now()->subMonth(),
            'date_fin' => now()->addMonths(2),
            'statut' => 'actif',
            'montant' => Ecole::TARIF_PREMIUM_TRIMESTRIEL,
        ]);

        // Toutes les données de démo générées par les seeders suivants sont
        // rattachées à cette école via Demo::ecoleId() (voir DatabaseSeeder).
        Demo::setEcole($ecole);
    }
}
