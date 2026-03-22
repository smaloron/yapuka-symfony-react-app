<?php

// =============================================================================
// Repository Task - Requêtes personnalisées pour les tâches
// =============================================================================
// Contient les requêtes DQL/QueryBuilder spécifiques :
//   - Récupérer les tâches d'un utilisateur
//   - Calculer les statistiques (nombre par statut, en retard, etc.)
// =============================================================================

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Récupère toutes les tâches d'un utilisateur, triées par date de création (récent d'abord).
     *
     * @return Task[]
     */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule les statistiques des tâches pour un utilisateur donné.
     * Retourne un tableau associatif avec les compteurs par statut.
     *
     * @return array{total: int, todo: int, in_progress: int, done: int, overdue: int}
     */
    public function getStatsByOwner(User $owner): array
    {
        // Comptage par statut en une seule requête SQL optimisée
        $results = $this->createQueryBuilder('t')
            ->select('t.status, COUNT(t.id) as count')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $owner)
            ->groupBy('t.status')
            ->getQuery()
            ->getResult();

        // Initialisation des compteurs à zéro
        $stats = [
            'total' => 0,
            'todo' => 0,
            'in_progress' => 0,
            'done' => 0,
            'overdue' => 0,
        ];

        // Remplissage à partir des résultats SQL
        foreach ($results as $row) {
            $stats[$row['status']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }

        // Comptage des tâches en retard (date d'échéance dépassée et non terminée)
        $stats['overdue'] = (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.dueDate < :today')
            ->andWhere('t.status != :done')
            ->setParameter('owner', $owner)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->setParameter('done', Task::STATUS_DONE)
            ->getQuery()
            ->getSingleScalarResult();

        return $stats;
    }
}
