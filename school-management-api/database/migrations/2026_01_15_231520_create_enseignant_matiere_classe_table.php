<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table de liaison : enseignant - matière - classe
     */
    public function up(): void
    {
        Schema::create('enseignant_matiere_classe', function (Blueprint $table) {
            $table->id();
            
            // Lien vers l'enseignant
            $table->unsignedBigInteger('enseignant_id');
            $table->index('enseignant_id');
            
            // Lien vers la matière
            $table->unsignedBigInteger('matiere_id');
            $table->index('matiere_id');
            
            // Lien vers la classe
            $table->unsignedBigInteger('classe_id');
            $table->index('classe_id');
            
            // Éviter les doublons
            $table->unique(['enseignant_id', 'matiere_id', 'classe_id'], 'emc_unique');
            
            $table->timestamps();
        });
    }

    /**
     * Supprime la table
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignant_matiere_classe');
    }
};