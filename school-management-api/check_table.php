<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Colonnes de la table 'eleves':\n";
$columns = Schema::getColumnListing('eleves');
foreach ($columns as $column) {
    echo "- $column\n";
}

echo "\nColonnes de la table 'users':\n";
$columns = Schema::getColumnListing('users');
foreach ($columns as $column) {
    echo "- $column\n";
}
