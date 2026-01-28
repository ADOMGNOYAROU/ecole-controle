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
        Schema::table('parents', function (Blueprint $table) {
            // Ajout des champs manquants s'ils n'existent pas déjà
            if (!Schema::hasColumn('parents', 'est_verifie')) {
                $table->boolean('est_verifie')->default(false)->after('profession');
            }
            
            if (!Schema::hasColumn('parents', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('est_verifie');
            }
            
            if (!Schema::hasColumn('parents', 'derniere_ip')) {
                $table->string('derniere_ip')->nullable()->after('remember_token');
            }
            
            if (!Schema::hasColumn('parents', 'derniere_connexion')) {
                $table->timestamp('derniere_connexion')->nullable()->after('derniere_ip');
            }
            
            if (!Schema::hasColumn('parents', 'notes')) {
                $table->text('notes')->nullable()->after('derniere_connexion');
            }
            
            // Modification des contraintes existantes
            if (Schema::hasColumn('parents', 'nom')) {
                $table->string('nom', 100)->change();
            }
            
            if (Schema::hasColumn('parents', 'prenom')) {
                $table->string('prenom', 100)->change();
            }
            
            if (Schema::hasColumn('parents', 'email')) {
                $table->string('email', 100)->change();
                // S'assurer que l'index unique existe
                if (!Schema::hasIndex('parents', 'parents_email_unique')) {
                    $table->unique('email', 'parents_email_unique');
                }
            }
            
            if (Schema::hasColumn('parents', 'telephone')) {
                $table->string('telephone', 20)->change();
            }
            
            // Ajout d'index pour les recherches fréquentes
            if (!Schema::hasIndex('parents', 'parents_nom_prenom_index')) {
                $table->index(['nom', 'prenom'], 'parents_nom_prenom_index');
            }
            
            if (!Schema::hasIndex('parents', 'parents_telephone_index')) {
                $table->index('telephone', 'parents_telephone_index');
            }
            
            if (!Schema::hasIndex('parents', 'parents_email_index')) {
                $table->index('email', 'parents_email_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            // Suppression des nouveaux champs
            $columnsToDrop = [
                'est_verifie',
                'email_verified_at',
                'derniere_ip',
                'derniere_connexion',
                'notes'
            ];
            
            if (Schema::hasColumns('parents', $columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Suppression des index
            if (Schema::hasIndex('parents', 'parents_nom_prenom_index')) {
                $table->dropIndex('parents_nom_prenom_index');
            }
            
            if (Schema::hasIndex('parents', 'parents_telephone_index')) {
                $table->dropIndex('parents_telephone_index');
            }
            
            if (Schema::hasIndex('parents', 'parents_email_index')) {
                $table->dropIndex('parents_email_index');
            }
            
            if (Schema::hasIndex('parents', 'parents_email_unique')) {
                $table->dropIndex('parents_email_unique');
            }
            
            // Rétablissement des anciennes tailles de colonnes
            if (Schema::hasColumn('parents', 'nom')) {
                $table->string('nom', 255)->change();
            }
            
            if (Schema::hasColumn('parents', 'prenom')) {
                $table->string('prenom', 255)->change();
            }
            
            if (Schema::hasColumn('parents', 'email')) {
                $table->string('email', 255)->change();
            }
            
            if (Schema::hasColumn('parents', 'telephone')) {
                $table->string('telephone', 255)->change();
            }
        });
    }
};
