<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test de la route /api/auth/login
$jsonData = json_encode([
    'email' => 'admin@example.com',
    'password' => 'password123'
]);

$request = \Illuminate\Http\Request::create('/api/auth/login', 'POST', [], [], [], [], $jsonData);
$request->headers->set('Content-Type', 'application/json');
$request->headers->set('Accept', 'application/json');

try {
    $response = $app->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
    
    // Vérifier si c'est une redirection
    if ($response->getStatusCode() === 302) {
        echo "Redirect to: " . $response->headers->get('Location') . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
