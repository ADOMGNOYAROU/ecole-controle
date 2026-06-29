<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EcoleSeeder::class,
            AnneeScolaireSeeder::class,
            MatiereSeeder::class,
            AdminUserSeeder::class,
            EnseignantSeeder::class,
            ClasseSeeder::class,
            EleveSeeder::class,
            TuteurSeeder::class,
            AffectationEnseignantSeeder::class,
            CreneauHoraireSeeder::class,
            NoteSeeder::class,
            PresenceSeeder::class,
            PaiementSeeder::class,
            AnnonceSeeder::class,
        ]);
    }
}
