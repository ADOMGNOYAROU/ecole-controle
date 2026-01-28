<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Eleve;
use App\Models\Enseignant;

class GeneratePasswordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mot de passe par défaut pour tous les utilisateurs
        $defaultPassword = 'password123';
        
        echo "Génération des mots de passe pour tous les utilisateurs...\n";
        
        // Mettre à jour le mot de passe pour tous les utilisateurs existants
        $users = User::whereNotNull('email')->get();
        
        foreach ($users as $user) {
            $user->password = Hash::make($defaultPassword);
            $user->save();
            
            echo "✅ {$user->name} ({$user->email}) - Mot de passe: {$defaultPassword}\n";
        }
        
        echo "\n🎉 Tous les utilisateurs ont maintenant le mot de passe: '{$defaultPassword}'\n";
        echo "\nIdentifiants de connexion:\n";
        
        // Afficher les identifiants par rôle
        $this->displayCredentialsByRole();
        
        echo "\n⚠️  N'oubliez pas de demander aux utilisateurs de changer leur mot de passe après la première connexion !\n";
    }
    
    /**
     * Afficher les identifiants par rôle
     */
    private function displayCredentialsByRole(): void
    {
        $roles = ['admin', 'enseignant', 'eleve', 'parent'];
        
        foreach ($roles as $role) {
            $users = User::where('role', $role)->get();
            
            if ($users->count() > 0) {
                echo "\n📋 {$role}s:\n";
                foreach ($users as $user) {
                    echo "   • Email: {$user->email} | Nom: {$user->name}\n";
                }
            }
        }
    }
}
