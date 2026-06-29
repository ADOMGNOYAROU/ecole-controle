<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un nouveau super-administrateur de la plateforme (sans école rattachée)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Entrez le nom du super-administrateur', 'Super Admin');
        $email = $this->ask('Entrez l\'email du super-administrateur', 'superadmin@example.com');
        $password = $this->secret('Entrez le mot de passe (min 8 caractères)');

        if (strlen($password) < 8) {
            $this->error('Le mot de passe doit contenir au moins 8 caractères');
            return 1;
        }

        $user = User::create([
            'ecole_id' => null,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->info('Super-administrateur créé avec succès !');
        $this->info('Email: ' . $email);
        $this->info('Mot de passe: [le mot de passe que vous avez choisi]');

        return 0;
    }
}
