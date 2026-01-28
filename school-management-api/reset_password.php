<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'jean.dupont@ecole.com';
$newPassword = 'password123'; // Le nouveau mot de passe que vous voulez définir

// Trouver l'utilisateur
$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    // Mettre à jour le mot de passe
    $user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
    $user->save();
    
    echo "✅ Le mot de passe a été réinitialisé avec succès pour l'utilisateur : " . $user->email . "\n";
    
    // Vérifier que la connexion fonctionne maintenant
    if (\Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $newPassword])) {
        echo "✅ Connexion réussie avec le nouveau mot de passe !\n";
    } else {
        echo "❌ La connexion a échoué après la réinitialisation.\n";
    }
} else {
    echo "❌ Aucun utilisateur trouvé avec l'email : " . $email . "\n";
}
