<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les classes d'abord
        $this->call([
            AdminUserSeeder::class,
            CreateClassesSeeder::class,
            CreateSampleUsersSeeder::class,
            GeneratePasswordsSeeder::class,
            ProfTitulaireSeeder::class,
            NotesTestSeeder::class,
        ]);
    }
}
