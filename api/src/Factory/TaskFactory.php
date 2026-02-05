<?php

// =============================================================================
// Factory Task (Foundry) - Génération de tâches de test
// =============================================================================
// Génère des tâches avec des données variées : statuts, priorités,
// dates d'échéance passées/futures, etc.
// =============================================================================

namespace App\Factory;

use App\Entity\Task;
use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Task>
 */
class TaskFactory extends PersistentProxyObjectFactory
{
    // Compteur pour des titres uniques
    private static int $counter = 0;

    // Liste de titres de tâches réalistes pour les fixtures
    private const array SAMPLE_TITLES = [
        'Configurer le pipeline CI/CD',
        'Rédiger la documentation API',
        'Corriger le bug d\'affichage',
        'Mettre à jour les dépendances',
        'Écrire les tests unitaires',
        'Déployer en staging',
        'Revoir la pull request',
        'Optimiser les requêtes SQL',
        'Configurer le monitoring',
        'Préparer la démo client',
        'Migrer la base de données',
        'Ajouter la pagination',
        'Implémenter le cache Redis',
        'Sécuriser les endpoints',
        'Créer les fixtures de test',
    ];

    public static function class(): string
    {
        return Task::class;
    }

    /**
     * Valeurs par défaut pour chaque tâche générée
     */
    protected function defaults(): array|callable
    {
        self::$counter++;
        $titleIndex = self::$counter % count(self::SAMPLE_TITLES);

        return [
            'title' => self::SAMPLE_TITLES[$titleIndex],
            'description' => 'Description de la tâche #' . self::$counter,
            // Statut aléatoire parmi les 3 possibles
            'status' => self::randomStatus(),
            // Priorité aléatoire
            'priority' => self::randomPriority(),
            // Date d'échéance : entre hier et dans 30 jours
            'dueDate' => new \DateTimeImmutable('+' . random_int(-1, 30) . ' days'),
            // Le owner doit être fourni lors de la création
            'owner' => UserFactory::new(),
        ];
    }

    /**
     * Retourne un statut aléatoire
     */
    private static function randomStatus(): string
    {
        $statuses = [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS, Task::STATUS_DONE];
        return $statuses[array_rand($statuses)];
    }

    /**
     * Retourne une priorité aléatoire
     */
    private static function randomPriority(): string
    {
        $priorities = [Task::PRIORITY_LOW, Task::PRIORITY_MEDIUM, Task::PRIORITY_HIGH];
        return $priorities[array_rand($priorities)];
    }
}
