<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up(): void
    {
        Schema::create('enseignants', function (Blueprint $table) {
            // Identifiant unique auto-incrémenté
            $table->id();
            
            // Clé étrangère vers la table users (relation one-to-one)
            $table->unsignedBigInteger('user_id')->unique();
            $table->index('user_id');
            
            // Matricule unique de l'enseignant
            $table->string('matricule')->unique();
            
            // Spécialité de l'enseignant (ex: Mathématiques, Physique, etc.)
            $table->string('specialite')->nullable();
            
            // Date d'embauche de l'enseignant
            $table->date('date_embauche')->nullable();
            
            // Statut de l'enseignant (actif ou inactif)
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            
            // Horodatages de création et de mise à jour
            $table->timestamps();
            
            // Index pour optimiser les recherches sur les colonnes fréquemment utilisées
            $table->index('matricule');
            $table->index('specialite');
            $table->index('statut');
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignants');
    }
};
