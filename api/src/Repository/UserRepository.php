<?php

// =============================================================================
// Repository User - Requêtes personnalisées pour les utilisateurs
// =============================================================================
// Ce repository permet d'ajouter des méthodes de requête spécifiques
// pour l'entité User (ex: recherche par email).
// =============================================================================

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
