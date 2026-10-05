<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('derniere_connexion_le')->nullable()->index();
        });

        // Notes du super administrateur sur une école (suivi commercial, support) : jamais visibles par l'école
        Schema::create('notes_internes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->foreignId('auteur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('contenu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes_internes');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['derniere_connexion_le']);
            $table->dropColumn('derniere_connexion_le');
        });
    }
};
