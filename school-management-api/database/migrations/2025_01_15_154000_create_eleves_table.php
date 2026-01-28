<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuté quand on lance "php artisan migrate"
     * Crée la table eleves
     */
    public function up(): void
    {
        Schema::create('eleves', function (Blueprint $table) {
            // Colonne ID (auto-increment, clé primaire)
            $table->id();
            
            // Informations de base de l'élève
            $table->string('nom');  // Nom de famille
            $table->string('prenom');  // Prénom
            $table->string('matricule')->unique();  // Numéro unique d'identification
            
            // Informations de naissance
            $table->date('date_naissance');  // Date de naissance
            $table->string('lieu_naissance')->nullable();  // Lieu de naissance (optionnel)
            
            // Sexe (M ou F uniquement)
            $table->enum('sexe', ['M', 'F']);
            
            // Lien avec la classe (on créera cette table après)
            $table->foreignId('classe_id')
                  ->nullable()
                  ->constrained('classes')
                  ->onDelete('set null');
            
            // Contact et adresse
            $table->string('parent_contact')->nullable();  // Téléphone du parent
            $table->text('adresse')->nullable();  // Adresse complète
            
            // Statut de l'élève (actif ou inactif)
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            
            // Dates de création et modification automatiques
            $table->timestamps();
            
            // Index pour optimiser les recherches fréquentes
            $table->index('matricule');
            $table->index('classe_id');
            $table->index('statut');
        });
    }

    /**
     * Exécuté quand on lance "php artisan migrate:rollback"
     * Supprime la table eleves
     */
    public function down(): void
    {
        Schema::dropIfExists('eleves');
    }
};
