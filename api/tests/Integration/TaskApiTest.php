<?php

// =============================================================================
// Tests d'intégration - API des tâches
// =============================================================================
// Ces tests vérifient le comportement complet de l'API :
//   - Requête HTTP → Contrôleur → Base de données → Réponse JSON
//
// Ils nécessitent une base de données de test (configurée dans .env.test).
//
// Exécution : php bin/phpunit tests/Integration/
// =============================================================================

namespace App\Tests\Integration;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TaskApiTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $entityManager;
    private ?string $jwtToken = null;

    /**
     * Initialisation avant chaque test :
     *   - Créer un client HTTP
     *   - Créer un utilisateur de test
     *   - Obtenir un token JWT
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Créer un utilisateur de test
        $this->createTestUser();

        // Se connecter pour obtenir un JWT
        $this->jwtToken = $this->getJwtToken();
    }

    /**
     * Crée un utilisateur de test en base
     */
    private function createTestUser(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('test@yapuka.dev');
        $user->setUsername('Test User');
        $user->setPassword($hasher->hashPassword($user, 'test1234'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Obtient un token JWT via le endpoint de login
     */
    private function getJwtToken(): string
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@yapuka.dev',
            'password' => 'test1234',
        ]));

        $response = json_decode($this->client->getResponse()->getContent(), true);

        return $response['token'];
    }

    /**
     * Helper : ajoute le header Authorization avec le JWT
     */
    private function authRequest(string $method, string $uri, array $data = []): void
    {
        $this->client->request($method, $uri, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->jwtToken,
        ], $data ? json_encode($data) : null);
    }

    // =========================================================================
    // Tests d'inscription
    // =========================================================================

    /**
     * Test : l'inscription avec des données valides retourne 201
     */
    public function testRegisterSuccess(): void
    {
        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'new@yapuka.dev',
            'username' => 'New User',
            'password' => 'password123',
        ]));

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $response);
        $this->assertEquals('new@yapuka.dev', $response['user']['email']);
    }

    /**
     * Test : l'inscription avec un email existant retourne 409
     */
    public function testRegisterDuplicateEmail(): void
    {
        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@yapuka.dev', // Email déjà utilisé
            'username' => 'Another User',
            'password' => 'password123',
        ]));

        $this->assertResponseStatusCodeSame(409);
    }

    // =========================================================================
    // Tests de connexion
    // =========================================================================

    /**
     * Test : la connexion avec de bons identifiants retourne un JWT
     */
    public function testLoginSuccess(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@yapuka.dev',
            'password' => 'test1234',
        ]));

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $response);
    }

    /**
     * Test : la connexion avec un mauvais mot de passe retourne 401
     */
    public function testLoginWrongPassword(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@yapuka.dev',
            'password' => 'wrong_password',
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    // =========================================================================
    // Tests CRUD des tâches
    // =========================================================================

    /**
     * Test : la liste des tâches est accessible avec un JWT valide
     */
    public function testListTasksAuthenticated(): void
    {
        $this->authRequest('GET', '/api/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
    }

    /**
     * Test : la liste des tâches retourne 401 sans JWT
     */
    public function testListTasksUnauthenticated(): void
    {
        $this->client->request('GET', '/api/tasks');

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Test : créer une tâche retourne 201 avec les données correctes
     */
    public function testCreateTask(): void
    {
        $this->authRequest('POST', '/api/tasks', [
            'title' => 'Ma première tâche',
            'description' => 'Description de test',
            'priority' => 'high',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Ma première tâche', $response['title']);
        $this->assertEquals('todo', $response['status']); // Statut par défaut
        $this->assertEquals('high', $response['priority']);
    }

    /**
     * Test : créer une tâche sans titre retourne 422
     */
    public function testCreateTaskWithoutTitle(): void
    {
        $this->authRequest('POST', '/api/tasks', [
            'description' => 'Pas de titre',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * Test : modifier une tâche met à jour les données
     */
    public function testUpdateTask(): void
    {
        // D'abord créer une tâche
        $this->authRequest('POST', '/api/tasks', [
            'title' => 'Tâche à modifier',
        ]);
        $created = json_decode($this->client->getResponse()->getContent(), true);

        // Puis la modifier
        $this->authRequest('PUT', '/api/tasks/' . $created['id'], [
            'status' => 'done',
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('done', $response['status']);
    }

    /**
     * Test : supprimer une tâche retourne 200
     */
    public function testDeleteTask(): void
    {
        // Créer une tâche
        $this->authRequest('POST', '/api/tasks', [
            'title' => 'Tâche à supprimer',
        ]);
        $created = json_decode($this->client->getResponse()->getContent(), true);

        // La supprimer
        $this->authRequest('DELETE', '/api/tasks/' . $created['id']);

        $this->assertResponseIsSuccessful();
    }

    // =========================================================================
    // Tests des statistiques
    // =========================================================================

    /**
     * Test : les statistiques retournent les compteurs attendus
     */
    public function testStats(): void
    {
        // Créer quelques tâches avec différents statuts
        $this->authRequest('POST', '/api/tasks', ['title' => 'Tâche 1']);
        $this->authRequest('POST', '/api/tasks', ['title' => 'Tâche 2']);

        // Récupérer les stats
        $this->authRequest('GET', '/api/tasks/stats');

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('todo', $response);
        $this->assertArrayHasKey('done', $response);
        $this->assertGreaterThanOrEqual(2, $response['total']);
    }

    /**
     * Nettoyage après chaque test
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager?->close();
        $this->entityManager = null;
    }
}
