<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
            $table->foreignId('trimestre_id')->constrained('trimestres')->cascadeOnDelete();
            $table->decimal('moyenne_generale', 5, 2)->nullable();
            $table->unsignedSmallInteger('rang')->nullable();
            $table->string('appreciation')->nullable();
            $table->string('chemin_pdf')->nullable();
            $table->timestamp('genere_le')->nullable();
            $table->timestamps();

            $table->unique(['eleve_id', 'trimestre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletins');
    }
};
