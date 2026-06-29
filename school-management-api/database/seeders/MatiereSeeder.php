<?php

namespace Database\Seeders;

use App\Models\Matiere;
use Illuminate\Database\Seeder;

class MatiereSeeder extends Seeder
{
    public function run(): void
    {
        $matieres = [
            ['nom' => 'Mathématiques', 'code' => 'MATH', 'coefficient_defaut' => 4],
            ['nom' => 'Français', 'code' => 'FR', 'coefficient_defaut' => 4],
            ['nom' => 'Histoire-Géographie', 'code' => 'HG', 'coefficient_defaut' => 3],
            ['nom' => 'Sciences de la Vie et de la Terre', 'code' => 'SVT', 'coefficient_defaut' => 2],
            ['nom' => 'Physique-Chimie', 'code' => 'PC', 'coefficient_defaut' => 3],
            ['nom' => 'Anglais', 'code' => 'ANG', 'coefficient_defaut' => 2],
            ['nom' => 'Éducation Physique et Sportive', 'code' => 'EPS', 'coefficient_defaut' => 1],
            ['nom' => 'Arts Plastiques', 'code' => 'ART', 'coefficient_defaut' => 1],
        ];

        foreach ($matieres as $matiere) {
            Matiere::create([...$matiere, 'ecole_id' => Demo::ecoleId()]);
        }
    }
}
