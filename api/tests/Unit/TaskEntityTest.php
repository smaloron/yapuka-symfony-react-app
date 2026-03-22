<?php

// =============================================================================
// Tests unitaires - Entité Task
// =============================================================================
// Ces tests vérifient la logique métier de l'entité Task
// SANS accéder à la base de données (tests rapides et isolés).
//
// Exécution : php bin/phpunit tests/Unit/
// =============================================================================

namespace App\Tests\Unit;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskEntityTest extends TestCase
{
    /**
     * Test : une nouvelle tâche a le statut "todo" par défaut.
     */
    public function testDefaultStatus(): void
    {
        $task = new Task();

        $this->assertEquals(Task::STATUS_TODO, $task->getStatus());
    }

    /**
     * Test : une nouvelle tâche a la priorité "medium" par défaut.
     */
    public function testDefaultPriority(): void
    {
        $task = new Task();

        $this->assertEquals(Task::PRIORITY_MEDIUM, $task->getPriority());
    }

    /**
     * Test : la date de création est remplie automatiquement.
     */
    public function testCreatedAtIsSet(): void
    {
        $task = new Task();

        $this->assertNotNull($task->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
    }

    /**
     * Test : une tâche sans date d'échéance n'est jamais en retard.
     */
    public function testTaskWithoutDueDateIsNotOverdue(): void
    {
        $task = new Task();
        $task->setDueDate(null);

        $this->assertFalse($task->isOverdue());
    }

    /**
     * Test : une tâche terminée n'est jamais en retard (même si date dépassée).
     */
    public function testDoneTaskIsNeverOverdue(): void
    {
        $task = new Task();
        // Date d'échéance dans le passé
        $task->setDueDate(new \DateTimeImmutable('-5 days'));
        // Mais la tâche est terminée
        $task->setStatus(Task::STATUS_DONE);

        $this->assertFalse($task->isOverdue());
    }

    /**
     * Test : une tâche non terminée avec une date passée est en retard.
     */
    public function testOverdueTask(): void
    {
        $task = new Task();
        $task->setDueDate(new \DateTimeImmutable('-1 day'));
        $task->setStatus(Task::STATUS_TODO);

        $this->assertTrue($task->isOverdue());
    }

    /**
     * Test : une tâche avec une date future n'est pas en retard.
     */
    public function testFutureTaskIsNotOverdue(): void
    {
        $task = new Task();
        $task->setDueDate(new \DateTimeImmutable('+7 days'));
        $task->setStatus(Task::STATUS_TODO);

        $this->assertFalse($task->isOverdue());
    }

    /**
     * Test : on peut assigner un propriétaire à une tâche.
     */
    public function testSetOwner(): void
    {
        $user = new User();
        $user->setEmail('test@yapuka.dev');

        $task = new Task();
        $task->setOwner($user);

        $this->assertSame($user, $task->getOwner());
    }

    /**
     * Test : le changement de statut met à jour updatedAt.
     */
    public function testStatusChangeUpdatesTimestamp(): void
    {
        $task = new Task();
        $originalDate = $task->getUpdatedAt();

        // Petite pause pour garantir un timestamp différent
        usleep(1000);

        $task->setStatus(Task::STATUS_DONE);

        $this->assertGreaterThanOrEqual(
            $originalDate,
            $task->getUpdatedAt()
        );
    }
}
