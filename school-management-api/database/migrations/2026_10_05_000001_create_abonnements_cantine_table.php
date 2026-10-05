<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecoles', function (Blueprint $table) {
            $table->decimal('prix_cantine_mois', 10, 2)->nullable()->after('prix_cantine');
        });

        // Un enregistrement = un élève a payé la cantine pour tout un mois
        Schema::create('abonnements_cantine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
            $table->date('mois');
            $table->decimal('montant', 10, 2);
            $table->foreignId('enregistre_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['eleve_id', 'mois']);
            $table->index(['ecole_id', 'mois']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements_cantine');

        Schema::table('ecoles', function (Blueprint $table) {
            $table->dropColumn('prix_cantine_mois');
        });
    }
};
