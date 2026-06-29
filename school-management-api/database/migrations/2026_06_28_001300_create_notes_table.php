<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
            $table->foreignId('matiere_id')->constrained('matieres')->cascadeOnDelete();
            $table->foreignId('enseignant_id')->constrained('enseignants')->cascadeOnDelete();
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('trimestre_id')->constrained('trimestres')->cascadeOnDelete();
            $table->enum('type', ['devoir', 'composition'])->default('devoir');
            $table->decimal('valeur', 5, 2);
            $table->decimal('bareme', 5, 2)->default(20);
            $table->decimal('coefficient', 4, 2)->default(1);
            $table->date('date_evaluation');
            $table->string('commentaire')->nullable();
            $table->timestamps();

            $table->index(['eleve_id', 'trimestre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
