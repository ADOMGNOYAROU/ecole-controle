<?php

namespace Database\Seeders;

use App\Models\Eleve;
use App\Models\Tuteur;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TuteurSeeder extends Seeder
{
    public function run(): void
    {
        $premiersEleves = Eleve::orderBy('id')->take(3)->get();

        foreach ($premiersEleves as $eleve) {
            $email = 'parent.'.strtolower($eleve->nom).'@ecole.test';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'ecole_id' => Demo::ecoleId(),
                    'name' => "Parent de {$eleve->prenom} {$eleve->nom}",
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_PARENT,
                ]
            );

            $tuteur = Tuteur::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'ecole_id' => Demo::ecoleId(),
                    'nom' => $eleve->nom,
                    'prenom' => 'Parent de '.$eleve->prenom,
                    'telephone' => '0600000'.str_pad((string) $eleve->id, 3, '0', STR_PAD_LEFT),
                    'email' => $email,
                ]
            );

            $tuteur->eleves()->syncWithoutDetaching([
                $eleve->id => ['lien_parente' => 'Parent', 'contact_principal' => true],
            ]);
        }
    }
}
