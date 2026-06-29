<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Matiere;
use Illuminate\Database\Seeder;

class AffectationEnseignantSeeder extends Seeder
{
    public function run(): void
    {
        $enseignants = Enseignant::all();
        $matieres = Matiere::all();
        $classes = Classe::all();

        foreach ($classes as $classe) {
            foreach ($matieres as $index => $matiere) {
                $enseignant = $enseignants[$index % $enseignants->count()];

                $enseignant->matieres()->attach($matiere->id, ['classe_id' => $classe->id]);
            }
        }
    }
}
