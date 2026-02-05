<?php

// =============================================================================
// Contrôleur des tâches - Endpoints personnalisés
// =============================================================================
// Ce contrôleur gère les endpoints qui nécessitent une logique métier
// au-delà du simple CRUD fourni par API Platform :
//   GET    /api/tasks       - Liste filtrée par propriétaire
//   POST   /api/tasks       - Création avec assignation automatique du owner
//   PUT    /api/tasks/{id}  - Modification avec vérification des droits
//   DELETE /api/tasks/{id}  - Suppression avec vérification des droits
//   GET    /api/tasks/stats - Statistiques personnelles
//
// Principe de sécurité : chaque utilisateur ne voit et modifie que SES tâches.
// =============================================================================

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/api/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskRepository $taskRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private CacheInterface $cache,
    ) {
    }

    // =========================================================================
    // GET /api/tasks - Liste des tâches de l'utilisateur connecté
    // =========================================================================
    #[Route('', name: 'api_tasks_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Récupérer l'utilisateur authentifié via le token JWT
        $user = $this->getUser();

        // Requête filtrée : uniquement les tâches de cet utilisateur
        $tasks = $this->taskRepository->findByOwner($user);

        // Sérialiser avec le groupe "task:read" (contrôle les champs exposés)
        $json = $this->serializer->serialize($tasks, 'json', ['groups' => 'task:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // =========================================================================
    // POST /api/tasks - Créer une nouvelle tâche
    // =========================================================================
    #[Route('', name: 'api_tasks_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        // Créer la tâche et assigner automatiquement le propriétaire
        $task = new Task();
        $task->setTitle($data['title'] ?? '');
        $task->setDescription($data['description'] ?? null);
        $task->setStatus($data['status'] ?? Task::STATUS_TODO);
        $task->setPriority($data['priority'] ?? Task::PRIORITY_MEDIUM);
        $task->setOwner($user);

        // Gérer la date d'échéance si fournie
        if (isset($data['dueDate'])) {
            $task->setDueDate(new \DateTimeImmutable($data['dueDate']));
        }

        // Valider les contraintes de l'entité
        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Persister en base
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        // Invalider le cache des statistiques (les stats doivent être recalculées)
        $this->cache->delete('task_stats_' . $user->getId());

        // Retourner la tâche créée
        $json = $this->serializer->serialize($task, 'json', ['groups' => 'task:read']);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    // =========================================================================
    // PUT /api/tasks/{id} - Modifier une tâche existante
    // =========================================================================
    #[Route('/{id}', name: 'api_tasks_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $task = $this->taskRepository->find($id);

        // Vérifier que la tâche existe
        if (!$task) {
            return $this->json(['error' => 'Tâche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est bien le propriétaire (sécurité !)
        if ($task->getOwner()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        // Appliquer les modifications
        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }
        if (isset($data['priority'])) {
            $task->setPriority($data['priority']);
        }
        if (array_key_exists('dueDate', $data)) {
            $task->setDueDate($data['dueDate'] ? new \DateTimeImmutable($data['dueDate']) : null);
        }

        // Mettre à jour le timestamp
        $task->setUpdatedAt(new \DateTimeImmutable());

        // Valider et sauvegarder
        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->flush();

        // Invalider le cache des stats
        $this->cache->delete('task_stats_' . $user->getId());

        $json = $this->serializer->serialize($task, 'json', ['groups' => 'task:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // =========================================================================
    // DELETE /api/tasks/{id} - Supprimer une tâche
    // =========================================================================
    #[Route('/{id}', name: 'api_tasks_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->getUser();
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Tâche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // Vérification des droits : seul le propriétaire peut supprimer
        if ($task->getOwner()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        // Invalider le cache
        $this->cache->delete('task_stats_' . $user->getId());

        return $this->json(['message' => 'Tâche supprimée.'], Response::HTTP_OK);
    }

    // =========================================================================
    // GET /api/tasks/stats - Statistiques des tâches
    // =========================================================================
    // Les stats sont mises en cache Redis pendant 60 secondes
    // pour éviter de recalculer à chaque requête.
    // =========================================================================
    #[Route('/stats', name: 'api_tasks_stats', methods: ['GET'], priority: 10)]
    public function stats(): JsonResponse
    {
        $user = $this->getUser();

        // Récupérer les stats depuis le cache Redis, ou les calculer
        $stats = $this->cache->get(
            'task_stats_' . $user->getId(),
            function (ItemInterface $item) use ($user) {
                // Durée de vie du cache : 60 secondes
                $item->expiresAfter(60);
                return $this->taskRepository->getStatsByOwner($user);
            }
        );

        return $this->json($stats);
    }
}
