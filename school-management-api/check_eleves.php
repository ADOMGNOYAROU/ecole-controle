<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\Eleve;

echo "Élèves existants dans la base:\n";
$eleves = Eleve::take(5)->get();

foreach ($eleves as $eleve) {
    echo "ID: {$eleve->id} - {$eleve->prenom} {$eleve->nom} - Email: " . ($eleve->email ?: 'non défini') . " - Classe: {$eleve->classe_id}\n";
}

if ($eleves->isEmpty()) {
    echo "Aucun élève trouvé dans la base.\n";
}
