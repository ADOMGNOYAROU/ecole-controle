<?php

namespace Database\Seeders;

use App\Models\Annonce;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnonceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->where('ecole_id', Demo::ecoleId())->first();

        $annonces = [
            ['titre' => 'Réunion parents-professeurs', 'contenu' => 'La réunion parents-professeurs du premier trimestre se tiendra le 15 décembre à 17h dans le grand amphithéâtre.', 'cible' => 'parents'],
            ['titre' => 'Reprise des cours', 'contenu' => 'Les cours reprendront normalement le 5 janvier après les vacances de fin d\'année.', 'cible' => 'tous'],
            ['titre' => 'Conseil pédagogique', 'contenu' => 'Le prochain conseil pédagogique aura lieu le premier mercredi du mois.', 'cible' => 'enseignants'],
        ];

        foreach ($annonces as $annonce) {
            Annonce::create([
                ...$annonce,
                'ecole_id' => Demo::ecoleId(),
                'auteur_id' => $admin->id,
                'date_publication' => now()->subDays(rand(0, 10)),
            ]);
        }
    }
}
