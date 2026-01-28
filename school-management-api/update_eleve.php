<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\Eleve;
use Illuminate\Support\Facades\Hash;

// Mettre à jour le premier élève avec un mot de passe
$eleve = Eleve::find(1);

if ($eleve) {
    $eleve->email = 'jean.dupont@ecole.com';
    $eleve->password = Hash::make('password123');
    $eleve->save();
    
    echo "✅ Compte élève mis à jour avec succès!\n";
    echo "📧 Email: jean.dupont@ecole.com\n";
    echo "🔑 Mot de passe: password123\n";
    echo "👤 Nom: {$eleve->prenom} {$eleve->nom}\n";
    echo "📚 Classe ID: {$eleve->classe_id}\n";
    echo "🆔 Élève ID: {$eleve->id}\n";
} else {
    echo "❌ Élève non trouvé.\n";
}
