<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\Eleve;
use Illuminate\Support\Facades\Hash;

// Créer un compte élève directement dans la table eleves
$eleve = Eleve::create([
    'email' => 'jean.dupont@ecole.com',
    'password' => Hash::make('password123'),
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    'classe_id' => 1,
    'matricule' => 'ELE' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
    'statut' => 'actif',
    'sexe' => 'M'
]);

echo "✅ Compte élève créé avec succès!\n";
echo "📧 Email: jean.dupont@ecole.com\n";
echo "🔑 Mot de passe: password123\n";
echo "🎓 Rôle: élève (géré dans la table eleves)\n";
echo "📚 Classe ID: 1\n";
echo "🆔 Matricule: " . $eleve->matricule . "\n";
