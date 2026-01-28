<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Eleve;
use App\Models\Enseignant;

class CreateSampleUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = 'password123';
        
        echo "Création d'utilisateurs exemples...\n";
        
        // Créer des enseignants
        $enseignants = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@ecole.com',
                'role' => 'enseignant',
                'telephone' => '0123456789',
                'specialite' => 'Mathématiques'
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@ecole.com',
                'role' => 'enseignant',
                'telephone' => '0234567890',
                'specialite' => 'Français'
            ],
            [
                'name' => 'Pierre Durand',
                'email' => 'pierre.durand@ecole.com',
                'role' => 'enseignant',
                'telephone' => '0345678901',
                'specialite' => 'Histoire-Géographie'
            ]
        ];
        
        foreach ($enseignants as $enseignantData) {
            $user = User::create([
                'name' => $enseignantData['name'],
                'email' => $enseignantData['email'],
                'password' => Hash::make($defaultPassword),
                'role' => $enseignantData['role'],
                'telephone' => $enseignantData['telephone'],
            ]);
            
            // Créer le profil enseignant
            $user->enseignant()->create([
                'matricule' => 'ENS' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'specialite' => $enseignantData['specialite'],
                'date_embauche' => now(),
            ]);
            
            echo "✅ Enseignant créé: {$enseignantData['name']} ({$enseignantData['email']})\n";
        }
        
        // Créer des étudiants
        $eleves = [
            [
                'name' => 'Alice Bernard',
                'email' => 'alice.bernard@ecole.com',
                'role' => 'eleve',
                'telephone' => '0456789012',
                'nom' => 'Bernard',
                'prenom' => 'Alice',
                'matricule' => 'ELEVE001',
                'date_naissance' => '2005-03-15',
                'sexe' => 'F',
                'classe_id' => 1
            ],
            [
                'name' => 'Thomas Petit',
                'email' => 'thomas.petit@ecole.com',
                'role' => 'eleve',
                'telephone' => '0567890123',
                'nom' => 'Petit',
                'prenom' => 'Thomas',
                'matricule' => 'ELEVE002',
                'date_naissance' => '2006-07-22',
                'sexe' => 'M',
                'classe_id' => 2
            ],
            [
                'name' => 'Sophie Leroy',
                'email' => 'sophie.leroy@ecole.com',
                'role' => 'eleve',
                'telephone' => '0678901234',
                'nom' => 'Leroy',
                'prenom' => 'Sophie',
                'matricule' => 'ELEVE003',
                'date_naissance' => '2005-11-08',
                'sexe' => 'F',
                'classe_id' => 1
            ]
        ];
        
        foreach ($eleves as $eleveData) {
            $user = User::create([
                'name' => $eleveData['name'],
                'email' => $eleveData['email'],
                'password' => Hash::make($defaultPassword),
                'role' => $eleveData['role'],
                'telephone' => $eleveData['telephone'],
            ]);
            
            // Créer le profil élève
            $user->eleve()->create([
                'nom' => $eleveData['nom'],
                'prenom' => $eleveData['prenom'],
                'matricule' => $eleveData['matricule'],
                'date_naissance' => $eleveData['date_naissance'],
                'sexe' => $eleveData['sexe'],
                'classe_id' => $eleveData['classe_id'],
            ]);
            
            echo "✅ Élève créé: {$eleveData['name']} ({$eleveData['email']})\n";
        }
        
        echo "\n🎉 Utilisateurs exemples créés avec succès !\n";
        echo "📝 Tous les utilisateurs ont le mot de passe: '{$defaultPassword}'\n";
    }
}
