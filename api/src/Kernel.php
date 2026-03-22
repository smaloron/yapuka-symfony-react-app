<?php

// =============================================================================
// Kernel Symfony - Point d'entrée du framework
// =============================================================================
// Le Kernel charge la configuration et les bundles de l'application.
// =============================================================================

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    // Le MicroKernelTrait charge automatiquement la config depuis config/
    use MicroKernelTrait;
}
