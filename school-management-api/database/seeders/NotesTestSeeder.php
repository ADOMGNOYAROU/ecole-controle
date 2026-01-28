<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Note;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Classe;
use Carbon\Carbon;

class NotesTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le premier élève et les matières
        $eleve = Eleve::first();
        $matieres = Matiere::all();
        
        if (!$eleve || $matieres->isEmpty()) {
            $this->command->info('Aucun élève ou matière trouvé. Veuillez exécuter les autres seeders d\'abord.');
            return;
        }

        // Générer des notes pour chaque matière sur les 3 derniers mois
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        foreach ($matieres as $matiere) {
            // Générer entre 3 et 5 notes par matière
            $numberOfNotes = rand(3, 5);
            
            for ($i = 0; $i < $numberOfNotes; $i++) {
                // Date aléatoire entre startDate et endDate
                $date = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );

                // Type d'évaluation aléatoire
                $types = ['devoir', 'interrogation', 'examen', 'composition'];
                $type = $types[array_rand($types)];

                // Note aléatoire entre 8 et 18 (avec une tendance à l'amélioration)
                $baseNote = 8 + ($i * 2); // Amélioration progressive
                $note = min(18, $baseNote + rand(-2, 4));
                
                // Trimestre basé sur la date
                $month = $date->month;
                if ($month >= 9 && $month <= 12) {
                    $trimestre = 1;
                } elseif ($month >= 1 && $month <= 4) {
                    $trimestre = 2;
                } else {
                    $trimestre = 3;
                }

                Note::create([
                    'eleve_id' => $eleve->id,
                    'matiere_id' => $matiere->id,
                    'classe_id' => $eleve->classe_id,
                    'type_evaluation' => $type,
                    'note' => $note,
                    'note_sur' => 20,
                    'date_evaluation' => $date,
                    'trimestre' => $trimestre,
                    'observation' => "Note de test - {$type}",
                ]);
            }
        }

        $this->command->info('Notes de test créées avec succès pour l\'élève: ' . $eleve->nom . ' ' . $eleve->prenom);
    }
}
