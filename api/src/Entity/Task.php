<?php

// =============================================================================
// Entité Task - Représente une tâche à réaliser
// =============================================================================
// Chaque tâche appartient à un utilisateur (owner) et possède :
//   - un titre, une description optionnelle
//   - un statut : "todo", "in_progress", "done"
//   - une priorité : "low", "medium", "high"
//   - une date d'échéance optionnelle
// =============================================================================

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
// Index sur le statut et le propriétaire pour accélérer les requêtes fréquentes
#[ORM\Index(columns: ['status'], name: 'idx_task_status')]
#[ORM\Index(columns: ['owner_id'], name: 'idx_task_owner')]
// Exposition via API Platform avec les opérations CRUD standard
// Note : les contrôleurs personnalisés gèrent la logique métier (filtrage par owner)
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['task:read']]),
        new Get(normalizationContext: ['groups' => ['task:read']]),
        new Post(
            normalizationContext: ['groups' => ['task:read']],
            denormalizationContext: ['groups' => ['task:write']],
        ),
        new Put(
            normalizationContext: ['groups' => ['task:read']],
            denormalizationContext: ['groups' => ['task:write']],
        ),
        new Delete(),
    ],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: false,
)]
class Task
{
    // --- Statuts possibles d'une tâche ---
    public const string STATUS_TODO = 'todo';
    public const string STATUS_IN_PROGRESS = 'in_progress';
    public const string STATUS_DONE = 'done';

    // --- Niveaux de priorité ---
    public const string PRIORITY_LOW = 'low';
    public const string PRIORITY_MEDIUM = 'medium';
    public const string PRIORITY_HIGH = 'high';

    // --- Identifiant unique ---
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?int $id = null;

    // --- Titre de la tâche (obligatoire, max 255 caractères) ---
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['task:read', 'task:write'])]
    private ?string $title = null;

    // --- Description optionnelle ---
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['task:read', 'task:write'])]
    private ?string $description = null;

    // --- Statut actuel de la tâche ---
    #[ORM\Column(length: 20)]
    #[Assert\Choice(
        choices: [self::STATUS_TODO, self::STATUS_IN_PROGRESS, self::STATUS_DONE],
        message: 'Statut invalide. Valeurs acceptées : todo, in_progress, done.'
    )]
    #[Groups(['task:read', 'task:write'])]
    private string $status = self::STATUS_TODO;

    // --- Niveau de priorité ---
    #[ORM\Column(length: 10)]
    #[Assert\Choice(
        choices: [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH],
        message: 'Priorité invalide. Valeurs acceptées : low, medium, high.'
    )]
    #[Groups(['task:read', 'task:write'])]
    private string $priority = self::PRIORITY_MEDIUM;

    // --- Date d'échéance (optionnelle) ---
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['task:read', 'task:write'])]
    private ?\DateTimeImmutable $dueDate = null;

    // --- Propriétaire de la tâche (relation ManyToOne vers User) ---
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['task:read'])]
    private ?User $owner = null;

    // --- Date de création (remplie automatiquement) ---
    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    // --- Date de dernière modification ---
    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    // =========================================================================
    // Getters et Setters
    // =========================================================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        // Mettre à jour la date de modification à chaque changement de statut
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // =========================================================================
    // Méthodes utilitaires
    // =========================================================================

    /**
     * Vérifie si la tâche est en retard (date d'échéance dépassée et non terminée)
     */
    public function isOverdue(): bool
    {
        if ($this->dueDate === null || $this->status === self::STATUS_DONE) {
            return false;
        }
        return $this->dueDate < new \DateTimeImmutable('today');
    }
}
