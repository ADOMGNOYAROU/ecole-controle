<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\User;
use App\Models\Eleve;
use Illuminate\Support\Facades\Hash;

// Créer un compte élève
$user = User::create([
    'name' => 'Jean Dupont',
    'email' => 'jean.dupont@ecole.com',
    'password' => Hash::make('password123'),
    'role' => 'eleve',
    'email_verified_at' => now()
]);

$eleve = Eleve::create([
    'user_id' => $user->id,
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    'classe_id' => 1,
    'matricule' => 'ELE' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
    'statut' => 'actif'
]);

echo "✅ Compte élève créé avec succès!\n";
echo "📧 Email: jean.dupont@ecole.com\n";
echo "🔑 Mot de passe: password123\n";
echo "🎓 Rôle: eleve\n";
echo "📚 Classe ID: 1\n";
