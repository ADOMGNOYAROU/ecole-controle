<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table classes
     */
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            // ID de la classe
            $table->id();
            
            // Nom de la classe (exemple: "6ème A", "CM2 B")
            $table->string('nom');
            
            // Niveau scolaire (exemple: "6ème", "CM2")
            $table->string('niveau');
            
            // Nombre maximum d'élèves (optionnel)
            $table->integer('effectif_max')->nullable();
            
            // Année scolaire (exemple: "2024-2025")
            $table->string('annee_scolaire');
            
            // Dates automatiques
            $table->timestamps();
        });
    }

    /**
     * Supprime la table classes
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
