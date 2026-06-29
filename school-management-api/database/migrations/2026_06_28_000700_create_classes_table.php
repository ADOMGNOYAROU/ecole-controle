<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->string('nom');
            $table->string('niveau')->nullable();
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->cascadeOnDelete();
            $table->foreignId('enseignant_principal_id')->nullable()->constrained('enseignants')->nullOnDelete();
            $table->unsignedSmallInteger('capacite')->nullable();
            $table->timestamps();

            $table->unique(['nom', 'annee_scolaire_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
