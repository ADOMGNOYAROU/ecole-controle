<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simuler une requête POST
$request = \Illuminate\Http\Request::create('/api/auth/login', 'POST', [
    'email' => 'jean.dupont@ecole.com',
    'password' => 'password123'
]);
$request->headers->set('Content-Type', 'application/json');

try {
    $response = $app->handle($request);
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
