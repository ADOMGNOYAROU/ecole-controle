<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Enseignant;
use Illuminate\Database\Seeder;

class ClasseSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeScolaire::active();
        $enseignants = Enseignant::all();

        $classes = [
            ['nom' => '6ème A', 'niveau' => '6ème', 'capacite' => 35],
            ['nom' => '5ème A', 'niveau' => '5ème', 'capacite' => 35],
            ['nom' => '4ème A', 'niveau' => '4ème', 'capacite' => 35],
            ['nom' => '3ème A', 'niveau' => '3ème', 'capacite' => 35],
        ];

        foreach ($classes as $index => $data) {
            Classe::create([
                ...$data,
                'ecole_id' => Demo::ecoleId(),
                'annee_scolaire_id' => $annee->id,
                'enseignant_principal_id' => $enseignants[$index % $enseignants->count()]->id,
            ]);
        }
    }
}
