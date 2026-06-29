<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Presence;
use App\Models\Trimestre;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PresenceSeeder extends Seeder
{
    public function run(): void
    {
        $trimestre = Trimestre::actuel();

        if (! $trimestre) {
            return;
        }

        $jours = collect();
        $date = $trimestre->date_debut->copy();
        $aujourdHui = Carbon::now();

        while ($date->lte($aujourdHui) && $date->lte($trimestre->date_fin) && $jours->count() < 20) {
            if ($date->isWeekday()) {
                $jours->push($date->copy());
            }
            $date->addDay();
        }

        foreach (Classe::with('elevesActifs')->get() as $classe) {
            $enseignantId = $classe->enseignant_principal_id;

            foreach ($jours as $jour) {
                foreach ($classe->elevesActifs as $eleve) {
                    $statut = match (true) {
                        mt_rand(1, 100) <= 85 => 'present',
                        mt_rand(1, 100) <= 70 => 'retard',
                        default => 'absent',
                    };

                    Presence::create([
                        'ecole_id' => Demo::ecoleId(),
                        'eleve_id' => $eleve->id,
                        'classe_id' => $classe->id,
                        'enseignant_id' => $enseignantId,
                        'trimestre_id' => $trimestre->id,
                        'date' => $jour->toDateString(),
                        'statut' => $statut,
                        'motif' => $statut === 'absent' ? 'Non communiqué' : null,
                    ]);
                }
            }
        }
    }
}
