<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table matieres
     */
    public function up(): void
    {
        Schema::create('matieres', function (Blueprint $table) {
            // ID de la matière
            $table->id();
            
            // Nom de la matière (ex: "Mathématiques")
            $table->string('nom');
            
            // Code unique (ex: "MATH")
            $table->string('code')->unique();
            
            // Coefficient de la matière
            $table->decimal('coefficient', 3, 1)->default(1.0);
            
            // Dates automatiques
            $table->timestamps();
        });
    }

    /**
     * Supprime la table matieres
     */
    public function down(): void
    {
        Schema::dropIfExists('matieres');
    }
};