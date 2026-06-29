<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Trimestre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        $trimestres = Trimestre::where('annee_scolaire_id', AnneeScolaire::active()->id)->orderBy('ordre')->get();
        $matieres = Matiere::all();
        $classes = Classe::with('elevesActifs')->get();

        foreach ($trimestres as $trimestre) {
            if ($trimestre->date_debut->isFuture()) {
                continue;
            }

            foreach ($classes as $classe) {
                foreach ($matieres as $matiere) {
                    $enseignantId = DB::table('enseignant_matiere_classe')
                        ->where('matiere_id', $matiere->id)
                        ->where('classe_id', $classe->id)
                        ->value('enseignant_id');

                    if (! $enseignantId) {
                        continue;
                    }

                    foreach ($classe->elevesActifs as $eleve) {
                        foreach (['devoir', 'composition'] as $type) {
                            Note::create([
                                'ecole_id' => Demo::ecoleId(),
                                'eleve_id' => $eleve->id,
                                'matiere_id' => $matiere->id,
                                'enseignant_id' => $enseignantId,
                                'classe_id' => $classe->id,
                                'trimestre_id' => $trimestre->id,
                                'type' => $type,
                                'valeur' => round(mt_rand(80, 195) / 10, 2),
                                'bareme' => 20,
                                'coefficient' => $type === 'composition' ? 2 : 1,
                                'date_evaluation' => $trimestre->date_debut->copy()->addDays(rand(5, 60)),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
