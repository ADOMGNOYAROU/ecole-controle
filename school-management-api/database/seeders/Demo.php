<?php

namespace Database\Seeders;

use App\Models\Ecole;

/**
 * Petit registre statique pour partager l'école de démo entre les seeders
 * appelés successivement par DatabaseSeeder (qui ne passe pas de paramètres).
 */
class Demo
{
    private static ?Ecole $ecole = null;

    public static function setEcole(Ecole $ecole): void
    {
        self::$ecole = $ecole;
    }

    public static function ecole(): Ecole
    {
        return self::$ecole ?? throw new \RuntimeException('EcoleSeeder doit être exécuté avant les autres seeders.');
    }

    public static function ecoleId(): int
    {
        return self::ecole()->id;
    }
}
