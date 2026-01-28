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
        Schema::create('etudiants', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique()->comment('Matricule unique de l\'étudiant');
            $table->string('prenom');
            $table->string('nom');
            $table->date('date_naissance');
            $table->enum('genre', ['M', 'F', 'Autre']);
            $table->string('lieu_naissance')->nullable();
            $table->string('adresse');
            $table->string('telephone');
            $table->string('email')->unique()->nullable();
            $table->string('photo')->nullable()->comment('Chemin vers la photo de profil');
            
            // Informations médicales
            $table->string('groupe_sanguin', 5)->nullable();
            $table->text('allergies')->nullable();
            $table->text('antecedents_medicaux')->nullable();
            $table->text('traitement_medical')->nullable();
            
            // Informations scolaires
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            
            // Informations de connexion
            $table->string('username')->unique();
            $table->string('password');
            $table->rememberToken();
            
            // Statut
            $table->enum('statut', ['actif', 'inactif', 'suspendu', 'diplomé', 'abandon'])->default('actif');
            $table->date('date_inscription')->default(now());
            $table->date('date_sortie')->nullable();
            $table->text('motif_sortie')->nullable();
            
            // Informations supplémentaires
            $table->string('nationalite')->default('Sénégalaise');
            $table->string('religion')->nullable();
            $table->string('groupe_ethnique')->nullable();
            $table->string('langue_maternelle')->nullable();
            
            // Informations de contact d'urgence
            $table->string('contact_urgence_nom')->nullable();
            $table->string('contact_urgence_telephone')->nullable();
            $table->string('contact_urgence_lien')->nullable();
            
            // Informations administratives
            $table->boolean('est_boursier')->default(false);
            $table->string('numero_bourse')->nullable();
            $table->text('notes_administratives')->nullable();
            
            // Horodatages
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['nom', 'prenom']);
            $table->index('classe_id');
            $table->index('parent_id');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};
