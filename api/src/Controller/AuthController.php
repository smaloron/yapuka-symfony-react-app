<?php

// =============================================================================
// Contrôleur d'authentification - Inscription et Connexion
// =============================================================================
// Gère deux endpoints publics :
//   POST /api/auth/register - Créer un nouveau compte utilisateur
//   POST /api/auth/login    - Obtenir un token JWT
//
// Ces routes sont exclues du firewall JWT (voir security.yaml).
// =============================================================================

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private ValidatorInterface $validator,
    ) {
    }

    // =========================================================================
    // POST /api/auth/register - Inscription d'un nouvel utilisateur
    // =========================================================================
    #[Route('/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // Décoder le corps JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que tous les champs requis sont présents
        if (!isset($data['email'], $data['password'], $data['username'])) {
            return $this->json(
                ['error' => 'Les champs email, username et password sont requis.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Vérifier que l'email n'est pas déjà utilisé
        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return $this->json(
                ['error' => 'Cet email est déjà utilisé.'],
                Response::HTTP_CONFLICT
            );
        }

        // Créer le nouvel utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);

        // Hasher le mot de passe (ne jamais stocker en clair !)
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Valider l'entité avec les contraintes Symfony (Assert\Email, etc.)
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json(
                ['errors' => $errorMessages],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Persister en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Générer immédiatement un JWT pour connecter l'utilisateur
        $token = $this->jwtManager->create($user);

        return $this->json([
            'message' => 'Inscription réussie.',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
            ],
        ], Response::HTTP_CREATED);
    }

    // =========================================================================
    // POST /api/auth/login - Connexion et obtention du JWT
    // =========================================================================
    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // Décoder le corps JSON
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return $this->json(
                ['error' => 'Les champs email et password sont requis.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Rechercher l'utilisateur par email
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        // Vérifier que l'utilisateur existe ET que le mot de passe est correct
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(
                ['error' => 'Identifiants invalides.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Générer le token JWT signé
        $token = $this->jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
            ],
        ]);
    }
}
