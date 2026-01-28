<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('enseignants') && Schema::hasColumn('enseignants', 'user_id')) {
            Schema::table('enseignants', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('enseignant_matiere_classe')) {
            Schema::table('enseignant_matiere_classe', function (Blueprint $table) {
                $table->foreign('enseignant_id')->references('id')->on('enseignants')->onDelete('cascade');
                $table->foreign('matiere_id')->references('id')->on('matieres')->onDelete('cascade');
                $table->foreign('classe_id')->references('id')->on('classes')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('presences') && Schema::hasColumn('presences', 'enseignant_id')) {
            Schema::table('presences', function (Blueprint $table) {
                $table->foreign('enseignant_id')->references('id')->on('enseignants')->nullOnDelete();
            });
        }

        if (Schema::hasTable('notes') && Schema::hasColumn('notes', 'enseignant_id')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->foreign('enseignant_id')->references('id')->on('enseignants')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notes')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->dropForeign(['enseignant_id']);
            });
        }

        if (Schema::hasTable('presences')) {
            Schema::table('presences', function (Blueprint $table) {
                $table->dropForeign(['enseignant_id']);
            });
        }

        if (Schema::hasTable('enseignant_matiere_classe')) {
            Schema::table('enseignant_matiere_classe', function (Blueprint $table) {
                $table->dropForeign(['enseignant_id']);
                $table->dropForeign(['matiere_id']);
                $table->dropForeign(['classe_id']);
            });
        }

        if (Schema::hasTable('enseignants')) {
            Schema::table('enseignants', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }
    }
};
