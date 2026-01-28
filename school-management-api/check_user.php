<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'jean.dupont@ecole.com';
$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    echo "Utilisateur trouvé :\n";
    echo "- Nom : " . $user->name . "\n";
    echo "- Email : " . $user->email . "\n";
    echo "- Rôle : " . $user->role . "\n";
    echo "- ID : " . $user->id . "\n";
    
    // Vérifier le mot de passe
    $password = 'password123';
    if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
        echo "✅ Le mot de passe est correct\n";
        
        // Essayer de se connecter
        if (\Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $password])) {
            echo "✅ Connexion réussie avec succès !\n";
        } else {
            echo "❌ Échec de la connexion. Raison : " . json_encode(error_get_last()) . "\n";
        }
    } else {
        echo "❌ Le mot de passe est incorrect\n";
        echo "Mot de passe haché dans la base : " . $user->password . "\n";
    }
} else {
    echo "❌ Aucun utilisateur trouvé avec l'email : " . $email . "\n";
    
    // Vérifier tous les utilisateurs existants
    echo "\nListe des utilisateurs existants :\n";
    $users = \App\Models\User::all();
    foreach ($users as $u) {
        echo "- " . $u->email . " (" . $u->role . ")\n";
    }
}
