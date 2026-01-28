<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Modifier la colonne role pour inclure prof_titulaire
            $table->enum('role', ['admin', 'enseignant', 'prof_titulaire', 'eleve', 'parent'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revenir à l'enum original
            $table->enum('role', ['admin', 'enseignant', 'eleve', 'parent'])->change();
        });
    }
};
