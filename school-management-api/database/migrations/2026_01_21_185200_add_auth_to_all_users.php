<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        // 1. Pour les élèves
        if (Schema::hasTable('eleves') && !Schema::hasColumn('eleves', 'email')) {
            Schema::table('eleves', function (Blueprint $table) {
                $table->string('email')->unique()->after('id');
                $table->string('password')->after('email');
                $table->rememberToken();
            });
        }

        // 2. Pour les enseignants
        if (Schema::hasTable('enseignants') && !Schema::hasColumn('enseignants', 'email')) {
            Schema::table('enseignants', function (Blueprint $table) {
                $table->string('email')->unique()->after('id');
                $table->string('password')->after('email');
                $table->rememberToken();
            });
        }

        // 3. Pour les parents (déjà créée précédemment)

        // Mise à jour des enregistrements existants
        $this->updateExistingUsers();
    }

    protected function updateExistingUsers()
    {
        // Mise à jour des élèves
        if (Schema::hasTable('eleves')) {
            $eleves = DB::table('eleves')->get();
            foreach ($eleves as $eleve) {
                $email = strtolower($eleve->prenom . '.' . $eleve->nom . '@ecole.com');
                $email = str_replace(' ', '.', $email);
                $password = Hash::make('eleve123');
                
                DB::table('eleves')
                    ->where('id', $eleve->id)
                    ->update([
                        'email' => $email,
                        'password' => $password
                    ]);
            }
        }

        // Mise à jour des enseignants
        if (Schema::hasTable('enseignants')) {
            $enseignants = DB::table('enseignants')->get();
            foreach ($enseignants as $enseignant) {
                $email = strtolower($enseignant->prenom . '.' . $enseignant->nom . '@ecole.com');
                $email = str_replace(' ', '.', $email);
                $password = Hash::make('prof123');
                
                DB::table('enseignants')
                    ->where('id', $enseignant->id)
                    ->update([
                        'email' => $email,
                        'password' => $password
                    ]);
            }
        }

        // Mise à jour des parents
        if (Schema::hasTable('parents')) {
            $parents = DB::table('parents')->whereNull('email')->get();
            foreach ($parents as $parent) {
                $email = strtolower($parent->prenom . '.' . $parent->nom . '@parent.com');
                $email = str_replace(' ', '.', $email);
                $password = Hash::make('parent123');
                
                DB::table('parents')
                    ->where('id', $parent->id)
                    ->update([
                        'email' => $email,
                        'password' => $password
                    ]);
            }
        }
    }

    public function down()
    {
        // Suppression des colonnes ajoutées
        if (Schema::hasColumn('eleves', 'email')) {
            Schema::table('eleves', function (Blueprint $table) {
                $table->dropColumn(['email', 'password', 'remember_token']);
            });
        }

        if (Schema::hasColumn('enseignants', 'email')) {
            Schema::table('enseignants', function (Blueprint $table) {
                $table->dropColumn(['email', 'password', 'remember_token']);
            });
        }
    }
};
