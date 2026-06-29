<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Trimestre;
use Illuminate\Database\Seeder;

class AnneeScolaireSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeScolaire::create([
            'ecole_id' => Demo::ecoleId(),
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-15',
            'date_fin' => '2026-07-05',
            'active' => true,
        ]);

        $trimestres = [
            ['nom' => '1er trimestre', 'ordre' => 1, 'date_debut' => '2025-09-15', 'date_fin' => '2025-12-19'],
            ['nom' => '2ème trimestre', 'ordre' => 2, 'date_debut' => '2026-01-05', 'date_fin' => '2026-03-27'],
            ['nom' => '3ème trimestre', 'ordre' => 3, 'date_debut' => '2026-04-06', 'date_fin' => '2026-07-05'],
        ];

        foreach ($trimestres as $trimestre) {
            Trimestre::create([...$trimestre, 'ecole_id' => Demo::ecoleId(), 'annee_scolaire_id' => $annee->id]);
        }
    }
}
