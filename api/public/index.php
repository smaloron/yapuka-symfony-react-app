<?php

// =============================================================================
// Point d'entrée HTTP de l'application Symfony
// =============================================================================
// Toutes les requêtes HTTP sont dirigées vers ce fichier par le serveur web.
// Il initialise le runtime Symfony et délègue le traitement au Kernel.
// =============================================================================

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
