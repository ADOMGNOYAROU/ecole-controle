<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'jean.dupont@ecole.com';
$password = 'password123';

$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    echo "Utilisateur trouvé : " . $user->name . "\n";
    
    // Afficher le hachage actuel du mot de passe
    echo "Hachage actuel : " . $user->password . "\n";
    
    // Vérifier si le mot de passe est correct
    if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
        echo "✅ Le mot de passe est correct !\n";
    } else {
        echo "❌ Le mot de passe est incorrect.\n";
        
        // Proposer de réinitialiser le mot de passe
        echo "\nVoulez-vous réinitialiser le mot de passe ? (o/n) ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        
        if(trim($line) == 'o'){
            $user->password = \Illuminate\Support\Facades\Hash::make($password);
            $user->save();
            echo "✅ Le mot de passe a été réinitialisé avec succès !\n";
        } else {
            echo "Le mot de passe n'a pas été modifié.\n";
        }
    }
} else {
    echo "❌ Aucun utilisateur trouvé avec l'email : " . $email . "\n";
}
