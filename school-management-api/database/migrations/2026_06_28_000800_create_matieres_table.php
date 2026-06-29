<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecole_id')->constrained('ecoles')->cascadeOnDelete();
            $table->string('nom');
            $table->string('code');
            $table->decimal('coefficient_defaut', 4, 2)->default(1);
            $table->timestamps();

            $table->unique(['ecole_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matieres');
    }
};
