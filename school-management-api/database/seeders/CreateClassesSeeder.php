<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classe;

class CreateClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            [
                'nom' => '6ème A',
                'niveau' => '6ème',
                'effectif_max' => 30,
                'annee_scolaire' => '2025-2026'
            ],
            [
                'nom' => '6ème B',
                'niveau' => '6ème',
                'effectif_max' => 30,
                'annee_scolaire' => '2025-2026'
            ],
            [
                'nom' => '5ème A',
                'niveau' => '5ème',
                'effectif_max' => 30,
                'annee_scolaire' => '2025-2026'
            ],
            [
                'nom' => '4ème A',
                'niveau' => '4ème',
                'effectif_max' => 30,
                'annee_scolaire' => '2025-2026'
            ],
            [
                'nom' => '3ème A',
                'niveau' => '3ème',
                'effectif_max' => 30,
                'annee_scolaire' => '2025-2026'
            ]
        ];

        foreach ($classes as $classeData) {
            Classe::create($classeData);
            echo "✅ Classe créée: {$classeData['nom']}\n";
        }

        echo "\n🎉 Classes créées avec succès !\n";
    }
}
