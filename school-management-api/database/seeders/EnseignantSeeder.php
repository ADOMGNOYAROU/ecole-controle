<?php

namespace Database\Seeders;

use App\Models\Enseignant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EnseignantSeeder extends Seeder
{
    public function run(): void
    {
        $enseignants = [
            ['nom' => 'Dubois', 'prenom' => 'Claire', 'specialite' => 'Mathématiques'],
            ['nom' => 'Martin', 'prenom' => 'Julien', 'specialite' => 'Français'],
            ['nom' => 'Leroy', 'prenom' => 'Sophie', 'specialite' => 'Histoire-Géographie'],
            ['nom' => 'Bernard', 'prenom' => 'Thomas', 'specialite' => 'Sciences'],
        ];

        foreach ($enseignants as $data) {
            $email = strtolower($data['prenom'].'.'.$data['nom']).'@ecole.test';

            $user = User::create([
                'ecole_id' => Demo::ecoleId(),
                'name' => "{$data['prenom']} {$data['nom']}",
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => User::ROLE_ENSEIGNANT,
            ]);

            Enseignant::create([
                ...$data,
                'ecole_id' => Demo::ecoleId(),
                'user_id' => $user->id,
                'email' => $email,
                'date_embauche' => now()->subYears(rand(1, 8)),
            ]);
        }
    }
}
