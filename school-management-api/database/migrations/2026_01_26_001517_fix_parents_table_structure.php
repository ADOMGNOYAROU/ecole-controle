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
        Schema::table('parents', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('parents', 'est_verifie')) {
                $table->boolean('est_verifie')->default(false)->after('profession');
            }
            
            if (!Schema::hasColumn('parents', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('est_verifie');
            }
            
            if (!Schema::hasColumn('parents', 'derniere_ip')) {
                $table->string('derniere_ip')->nullable()->after('remember_token');
            }
            
            if (!Schema::hasColumn('parents', 'derniere_connexion')) {
                $table->timestamp('derniere_connexion')->nullable()->after('derniere_ip');
            }
            
            if (!Schema::hasColumn('parents', 'notes')) {
                $table->text('notes')->nullable()->after('derniere_connexion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn([
                'est_verifie',
                'email_verified_at',
                'derniere_ip',
                'derniere_connexion',
                'notes'
            ]);
        });
    }
};
