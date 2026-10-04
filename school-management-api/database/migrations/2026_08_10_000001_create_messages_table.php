<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->foreignId('expediteur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('destinataire_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('eleve_id')->nullable()->constrained('eleves')->nullOnDelete();
            $table->text('contenu');
            $table->boolean('lu')->default(false);
            $table->timestamps();

            $table->index(['expediteur_id', 'destinataire_id']);
            $table->index(['destinataire_id', 'lu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
