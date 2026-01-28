<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table notes
     */
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            
            // Lien vers l'élève
            $table->foreignId('eleve_id')
                  ->constrained('eleves')
                  ->onDelete('cascade');
            
            // Lien vers la matière
            $table->foreignId('matiere_id')
                  ->constrained('matieres')
                  ->onDelete('cascade');
            
            // Lien vers la classe
            $table->foreignId('classe_id')
                  ->constrained('classes')
                  ->onDelete('cascade');
            
            // Type d'évaluation
            $table->enum('type_evaluation', ['devoir', 'interrogation', 'examen', 'composition']);
            
            // La note obtenue
            $table->decimal('note', 5, 2);
            
            // Note sur combien (ex: 20)
            $table->decimal('note_sur', 5, 2)->default(20);
            
            // Date de l'évaluation
            $table->date('date_evaluation');
            
            // Trimestre (1, 2 ou 3)
            $table->integer('trimestre');
            
            // Enseignant qui a saisi la note
            $table->unsignedBigInteger('enseignant_id')->nullable();
            $table->index('enseignant_id');
            
            // Observation (optionnelle)
            $table->text('observation')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Supprime la table notes
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};