<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table presences
     */
    public function up(): void
    {
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            
            // Lien vers l'élève
            $table->foreignId('eleve_id')
                  ->constrained('eleves')
                  ->onDelete('cascade');
            
            // Lien vers la classe
            $table->foreignId('classe_id')
                  ->constrained('classes')
                  ->onDelete('cascade');
            
            // Date de la présence
            $table->date('date');
            
            // Statut (présent, absent, retard, absent_justifie)
            $table->enum('statut', ['present', 'absent', 'retard', 'absent_justifie'])
                  ->default('present');
            
            // Motif d'absence (optionnel)
            $table->text('motif')->nullable();
            
            // Enseignant qui a marqué la présence
            $table->unsignedBigInteger('enseignant_id')->nullable();
            $table->index('enseignant_id');
            
            // Éviter les doublons (un élève ne peut avoir qu'une seule présence par jour)
            $table->unique(['eleve_id', 'date'], 'presence_unique');
            
            $table->timestamps();
        });
    }

    /**
     * Supprime la table presences
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};