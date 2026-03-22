<?php

// =============================================================================
// Entité User - Représente un utilisateur de l'application
// =============================================================================
// Cette entité est mappée à la table "users" dans PostgreSQL.
// Elle implémente les interfaces Symfony pour l'authentification.
// L'attribut #[ApiResource] expose automatiquement un CRUD via API Platform.
// =============================================================================

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
// Contrainte d'unicité : un seul compte par email
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
// API Platform : on expose uniquement la lecture (GET), pas la création
// L'inscription se fait via un contrôleur dédié (AuthController)
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['user:read']]),
        new GetCollection(normalizationContext: ['groups' => ['user:read']]),
    ],
    paginationEnabled: false,
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // --- Identifiant unique auto-incrémenté ---
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'task:read'])]
    private ?int $id = null;

    // --- Email de l'utilisateur (sert aussi de login) ---
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email n'est pas valide.")]
    #[Groups(['user:read', 'task:read'])]
    private ?string $email = null;

    // --- Nom affiché dans l'interface ---
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Groups(['user:read', 'task:read'])]
    private ?string $username = null;

    // --- Rôles Symfony (ROLE_USER par défaut) ---
    #[ORM\Column]
    private array $roles = [];

    // --- Mot de passe hashé (jamais exposé en lecture) ---
    #[ORM\Column]
    private ?string $password = null;

    // --- Relation : un utilisateur possède plusieurs tâches ---
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'owner', cascade: ['remove'])]
    private Collection $tasks;

    // --- Date de création du compte ---
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // =========================================================================
    // Getters et Setters
    // =========================================================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    // --- Méthode requise par UserInterface ---
    // Identifiant unique pour l'authentification (ici, l'email)
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Chaque utilisateur a au minimum ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    // --- Méthode requise par UserInterface ---
    // Permet de nettoyer les données sensibles en mémoire
    public function eraseCredentials(): void
    {
        // Rien à nettoyer ici
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
