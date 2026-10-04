<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecoles', function (Blueprint $table) {
            $table->decimal('prix_cantine', 10, 2)->nullable()->after('plan');
        });

        Schema::table('eleves', function (Blueprint $table) {
            $table->boolean('inscrit_cantine')->default(false)->after('statut');
        });

        // Un enregistrement = un élève a payé la cantine pour une journée donnée
        Schema::create('paiements_cantine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('montant', 10, 2);
            $table->foreignId('enregistre_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['eleve_id', 'date']);
            $table->index(['ecole_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements_cantine');

        Schema::table('eleves', function (Blueprint $table) {
            $table->dropColumn('inscrit_cantine');
        });

        Schema::table('ecoles', function (Blueprint $table) {
            $table->dropColumn('prix_cantine');
        });
    }
};
