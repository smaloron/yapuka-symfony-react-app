<?php

// =============================================================================
// Listener Doctrine - Événements sur les tâches
// =============================================================================
// Ce listener écoute les événements Doctrine (persist, update, remove)
// sur l'entité Task pour déclencher des actions annexes :
//   - Logger les modifications
//   - Publier des notifications (via Redis en production)
//
// En production, ce listener publierait dans Redis pub/sub,
// et le contrôleur SSE lirait depuis Redis.
// =============================================================================

namespace App\EventListener;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: Events::postPersist, entity: Task::class)]
#[AsEntityListener(event: Events::postUpdate, entity: Task::class)]
#[AsEntityListener(event: Events::postRemove, entity: Task::class)]
class TaskNotificationListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Déclenché après la création d'une nouvelle tâche.
     */
    public function postPersist(Task $task): void
    {
        $this->logger->info('Nouvelle tâche créée', [
            'task_id' => $task->getId(),
            'title' => $task->getTitle(),
            'owner' => $task->getOwner()?->getEmail(),
        ]);

        // TODO: En production, publier dans Redis pub/sub
        // $this->redis->publish('notifications', json_encode([
        //     'type' => 'task_created',
        //     'taskId' => $task->getId(),
        //     'message' => 'Nouvelle tâche : ' . $task->getTitle(),
        // ]));
    }

    /**
     * Déclenché après la modification d'une tâche.
     */
    public function postUpdate(Task $task): void
    {
        $this->logger->info('Tâche modifiée', [
            'task_id' => $task->getId(),
            'status' => $task->getStatus(),
        ]);
    }

    /**
     * Déclenché après la suppression d'une tâche.
     */
    public function postRemove(Task $task): void
    {
        $this->logger->info('Tâche supprimée', [
            'task_id' => $task->getId(),
            'title' => $task->getTitle(),
        ]);
    }
}
