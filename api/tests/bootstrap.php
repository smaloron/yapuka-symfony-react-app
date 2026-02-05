<?php

// =============================================================================
// Bootstrap des tests - Chargé avant l'exécution des tests PHPUnit
// =============================================================================

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Charger les variables d'environnement depuis .env.test
if (file_exists(dirname(__DIR__).'/.env.test')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');
}
