<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Test simple pour vérifier si l'AuthController fonctionne
    $controller = new \App\Http\Controllers\Api\AuthController();
    
    // Créer une requête factice avec les bonnes données
    $requestData = [
        'email' => 'admin@example.com',
        'password' => 'password123'
    ];
    
    $request = \Illuminate\Http\Request::create('/api/auth/login', 'POST', $requestData);
    
    echo "Testing AuthController...\n";
    
    // Créer un LoginRequest valide
    $loginRequest = \App\Http\Requests\Auth\LoginRequest::createFromBase($request);
    
    // Tester la méthode login directement
    $response = $controller->login($loginRequest);
    
    echo "Success! Response: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
