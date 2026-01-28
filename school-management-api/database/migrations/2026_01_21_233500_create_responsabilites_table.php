<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsabilites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enseignant_id')->constrained('enseignants')->onDelete('cascade');
            
            // Type de responsabilité (cours, surveillance, activité, etc.)
            $table->string('type');
            
            // Description détaillée de la responsabilité
            $table->text('description');
            
            // Période de la responsabilité
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            
            // Statut (actif, terminé, annulé)
            $table->enum('statut', ['actif', 'termine', 'annule'])->default('actif');
            
            // Liens optionnels vers une classe et/ou une matière
            $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('matiere_id')->nullable()->constrained('matieres')->onDelete('set null');
            
            // Informations complémentaires
            $table->text('commentaires')->nullable();
            
            // Horaires récurrents (stockés en JSON)
            $table->json('horaires')->nullable();
            
            // Informations de suivi
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour les recherches courantes
            $table->index(['enseignant_id', 'statut']);
            $table->index(['type', 'statut']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsabilites');
    }
};
