<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EleveSeeder extends Seeder
{
    public function run(): void
    {
        $prenoms = ['Léa', 'Hugo', 'Emma', 'Nathan', 'Chloé', 'Lucas', 'Manon', 'Louis', 'Camille', 'Maël', 'Inès', 'Adam'];
        $noms = ['Petit', 'Robert', 'Richard', 'Durand', 'Moreau', 'Simon', 'Laurent', 'Lefebvre', 'Michel', 'Garcia', 'David', 'Bertrand'];

        $classes = Classe::all();
        $compteur = 1;

        foreach ($classes as $classe) {
            for ($i = 0; $i < 6; $i++) {
                $prenom = $prenoms[array_rand($prenoms)];
                $nom = $noms[array_rand($noms)];
                $matricule = sprintf('ELV%04d', $compteur);
                $email = strtolower($prenom.'.'.$nom.$compteur).'@ecole.test';

                $eleve = Eleve::create([
                    'ecole_id' => Demo::ecoleId(),
                    'matricule' => $matricule,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'sexe' => rand(0, 1) ? 'M' : 'F',
                    'date_naissance' => now()->subYears(11 + rand(0, 4))->subDays(rand(0, 365)),
                    'classe_id' => $classe->id,
                    'email' => $email,
                    'statut' => 'actif',
                    'date_inscription' => $classe->anneeScolaire->date_debut,
                ]);

                if ($compteur <= 2) {
                    $user = User::create([
                        'ecole_id' => Demo::ecoleId(),
                        'name' => "{$prenom} {$nom}",
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'role' => User::ROLE_ELEVE,
                    ]);

                    $eleve->update(['user_id' => $user->id]);
                }

                $compteur++;
            }
        }
    }
}
