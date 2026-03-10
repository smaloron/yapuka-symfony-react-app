<?php

namespace App\Tests\Behat;

use App\Entity\User;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AnonymousContext implements Context
{

    private ?KernelBrowser $client;
    private ?array $responseData;

    private ?string $jwtToken = null;

    public function __construct(
        private KernelInterface $kernel,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher

    ) {
        $this->client = new KernelBrowser($this->kernel);
    }

    /**
     * @Given un utilisateur existe avec l'email :email et le mot de passe :password
     * @throws \Exception
     */
    public function unUtilisateurExisteAvecEmailEtMotDePasse(string $email, string $password): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => $password,
        ], JSON_THROW_ON_ERROR));

        $response = json_decode($this->client->getResponse()->getContent(), true);

        if (!isset($response['token'])) {
            throw new \RuntimeException(
                'Impossible d\'obtenir un JWT : ' . $this->client->getResponse()->getContent()
            );
        }

        $this->jwtToken = $response['token'];

    }

    /**
     * @When j'envoie une requête authentifiée GET sur :url
     */
    public function jEnvoieUneRequeteAuthentifieeGetSur(string $url): void
    {

        $this->client->request('GET', $url, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->jwtToken,
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->responseData = json_decode(
            $this->client->getResponse()->getContent(),
            true
        );
    }

    /**
     * @Then la réponse contient un utilisateur avec l'email :email
     */
    public function laReponseContientUnUtilisateurAvecEmail(string $email): void
    {
        $content = $this->responseData;

        // Gérer le cas où la réponse est un tableau (collection) ou un objet unique
        $users = isset($content[0]) ? $content : [$content];

        foreach ($users as $user) {
            if (isset($user['email']) && $user['email'] === $email) {
                return;
            }
        }

        throw new \RuntimeException(
            sprintf('Aucun utilisateur avec l\'email "%s" trouvé dans la réponse.', $email)
        );
    }


    /**
     * @throws \JsonException
     */
    public function authenticate(string $email, string $password): void
    {


    }

    /**
     * @return void
     * @BeforeScenario
     */
    function avantChaqueScenario()
    {
        $this->responseData = [];
    }

    /**
     * @When j'envoie une requête http GET sur :url sans être authentifié
     */
    public function jenvoieUneRequetHttpSansEtreAuthentife(string $url): void
    {

        $this->client->request(
            'GET',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->responseData = json_decode(
            $this->client->getResponse()->getContent(),
            true
        );
    }

    /**
     * @return void
     * @Then le code de réponse est :code
     */
    public function leCodeDeReponseEst(int $code): void
    {
        $actualCode = $this->client->getResponse()->getStatusCode();
        if ($actualCode != $code) {
            throw new RuntimeException(
                "codes attendu : {$code}, reçu : {$actualCode}"
            );
        }
    }
}
