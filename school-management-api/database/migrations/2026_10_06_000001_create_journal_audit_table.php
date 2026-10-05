<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Journal d'audit : une ligne par action, jamais modifiée ni supprimée par l'application
        // (seule la purge des entrées de plus de 12 mois les efface).
        Schema::create('journal_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->nullable()->constrained('ecoles')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_nom')->nullable();
            $table->string('user_role', 30)->nullable();
            $table->string('action', 40);
            $table->string('objet_type', 60)->nullable();
            $table->unsignedBigInteger('objet_id')->nullable();
            $table->string('objet_libelle')->nullable();
            $table->json('changements')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('appareil')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ecole_id', 'created_at']);
            $table->index(['objet_type', 'objet_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_audit');
    }
};
