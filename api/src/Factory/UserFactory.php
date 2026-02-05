<?php

// =============================================================================
// Factory User (Foundry) - Génération de données de test
// =============================================================================
// Foundry permet de créer facilement des objets de test.
// Cette factory génère des utilisateurs avec des données réalistes
// pour les fixtures et les tests automatisés.
//
// Utilisation :
//   UserFactory::createOne()              → crée 1 utilisateur
//   UserFactory::createMany(10)           → crée 10 utilisateurs
//   UserFactory::createOne(['email' => 'admin@test.com'])  → personnalisé
// =============================================================================

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
class UserFactory extends PersistentProxyObjectFactory
{
    // Compteur pour générer des emails uniques
    private static int $counter = 0;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * Valeurs par défaut pour chaque utilisateur généré.
     * Ces valeurs peuvent être surchargées lors de la création.
     */
    protected function defaults(): array|callable
    {
        self::$counter++;

        return [
            'email' => 'user' . self::$counter . '@yapuka.dev',
            'username' => 'Utilisateur ' . self::$counter,
            'roles' => ['ROLE_USER'],
            // Le mot de passe sera hashé dans initialize()
            'password' => 'password',
        ];
    }

    /**
     * Hook exécuté après l'instanciation mais avant la persistance.
     * Utilisé ici pour hasher le mot de passe.
     */
    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user): void {
            // Hasher le mot de passe en clair
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPassword())
            );
        });
    }
}
