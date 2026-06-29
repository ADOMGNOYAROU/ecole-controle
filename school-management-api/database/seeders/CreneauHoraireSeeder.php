<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\CreneauHoraire;
use App\Models\Matiere;
use Illuminate\Database\Seeder;

class CreneauHoraireSeeder extends Seeder
{
    public function run(): void
    {
        $matieres = Matiere::all();

        foreach (Classe::all() as $classe) {
            $heure = 8;

            foreach (range(1, 5) as $jour) {
                foreach ($matieres->random(min(3, $matieres->count())) as $matiere) {
                    $enseignant = $matiere->enseignants()->wherePivot('classe_id', $classe->id)->first();

                    CreneauHoraire::create([
                        'ecole_id' => Demo::ecoleId(),
                        'classe_id' => $classe->id,
                        'matiere_id' => $matiere->id,
                        'enseignant_id' => $enseignant?->id,
                        'jour_semaine' => $jour,
                        'heure_debut' => sprintf('%02d:00', $heure),
                        'heure_fin' => sprintf('%02d:00', $heure + 1),
                        'salle' => 'Salle '.rand(1, 12),
                    ]);

                    $heure += 1;
                }

                $heure = 8;
            }
        }
    }
}
