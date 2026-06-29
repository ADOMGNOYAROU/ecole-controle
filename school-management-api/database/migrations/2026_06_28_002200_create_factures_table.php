<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->foreignId('abonnement_id')->nullable()->constrained('abonnements')->nullOnDelete();
            $table->decimal('montant', 10, 2);
            $table->date('date_echeance');
            $table->enum('statut', ['en_attente', 'payee', 'en_retard', 'annulee'])->default('en_attente')->index();
            $table->string('methode_paiement')->nullable();
            $table->string('reference_transaction')->nullable();
            $table->timestamp('payee_le')->nullable();
            $table->foreignId('confirmee_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
