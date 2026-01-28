<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class GeneratePasswordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:passwords {--password= : Le mot de passe à définir (défaut: password123)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer un mot de passe pour tous les utilisateurs existants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $password = $this->option('password') ?? 'password123';
        
        $this->info("Génération des mots de passe pour tous les utilisateurs...");
        $this->info("Mot de passe utilisé: '{$password}'");
        $this->newLine();
        
        // Récupérer tous les utilisateurs avec un email
        $users = User::whereNotNull('email')->get();
        
        if ($users->count() === 0) {
            $this->warn("Aucun utilisateur trouvé avec un email.");
            return;
        }
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            $user->password = Hash::make($password);
            $user->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("🎉 Tous les {$users->count()} utilisateurs ont maintenant le mot de passe: '{$password}'");
        $this->newLine();
        
        // Afficher les identifiants par rôle
        $this->displayCredentialsByRole();
        
        $this->newLine();
        $this->warn("⚠️  N'oubliez pas de demander aux utilisateurs de changer leur mot de passe après la première connexion !");
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
                $this->info("📋 " . ucfirst($role) . "s:");
                foreach ($users as $user) {
                    $this->line("   • Email: {$user->email} | Nom: {$user->name}");
                }
                $this->newLine();
            }
        }
    }
}
