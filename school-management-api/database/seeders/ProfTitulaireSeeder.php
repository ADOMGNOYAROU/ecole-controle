<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Enseignant;
use App\Models\Classe;
use Illuminate\Support\Facades\Hash;

class ProfTitulaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur pour le professeur titulaire
        $user = User::create([
            'name' => 'Martin Dubois',
            'email' => 'martin.dubois@ecole.com',
            'password' => Hash::make('password123'),
            'role' => 'prof_titulaire', // Rôle de professeur titulaire
            'telephone' => '0123456789',
        ]);

        // Créer le profil enseignant correspondant
        $enseignant = Enseignant::create([
            'email' => 'martin.dubois@ecole.com',
            'password' => Hash::make('password123'),
            'matricule' => 'PROF001',
            'specialite' => 'Mathématiques',
            'date_embauche' => '2020-09-01',
            'statut' => 'actif',
            'user_id' => $user->id,
        ]);

        // Assigner le professeur comme titulaire de la première classe disponible
        $classe = Classe::first();
        if ($classe) {
            $classe->enseignant_id = $enseignant->id;
            $classe->save();
            
            $this->command->info('Professeur titulaire Martin Dubois assigné à la classe: ' . $classe->nom);
        }

        $this->command->info('Compte professeur titulaire créé avec succès:');
        $this->command->info('Email: martin.dubois@ecole.com');
        $this->command->info('Mot de passe: password123');
        $this->command->info('Rôle: prof_titulaire');
    }
}
