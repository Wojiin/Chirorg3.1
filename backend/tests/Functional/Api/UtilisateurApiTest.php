<?php

namespace App\Tests\Functional\Api;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

final class UtilisateurApiTest extends AuthenticatedApiTestCase
{
    public function testAuthenticatedUserCanReadProfileAndChangePassword(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: []);
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));

        $response = $client->request('GET', '/api/me');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['email' => $utilisateur->getEmail(), 'actif' => true]);
        self::assertArrayNotHasKey('password', $response->toArray());

        $client->request('PATCH', '/api/me/mot-de-passe', [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['motDePasseActuel' => 'incorrect', 'nouveauMotDePasse' => 'New-password-84!'],
        ]);
        self::assertResponseStatusCodeSame(422);

        $client->request('PATCH', '/api/me/mot-de-passe', [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['motDePasseActuel' => $password, 'nouveauMotDePasse' => 'New-password-84!'],
        ]);
        self::assertResponseIsSuccessful();

        $client->request('POST', '/api/auth/refresh');
        self::assertResponseStatusCodeSame(401);

        $freshClient = $this->createApiClient();
        $freshClient->request('POST', '/api/auth/login', ['json' => ['email' => $utilisateur->getEmail(), 'password' => $password]]);
        self::assertResponseStatusCodeSame(401);
        $this->login($freshClient, $utilisateur, 'New-password-84!');

        $this->removeUtilisateur($utilisateur);
    }

    public function testOnlyAdminCanManageUtilisateurs(): void
    {
        $client = $this->createApiClient();
        [$admin, $adminPassword] = $this->persistUtilisateur();
        $this->useBearerToken($client, $this->login($client, $admin, $adminPassword));

        $client->request('POST', '/api/utilisateurs', [
            'json' => ['email' => 'faible@chirorg.local', 'motDePasse' => 'faible'],
        ]);
        self::assertResponseStatusCodeSame(422);

        $email = 'api-'.bin2hex(random_bytes(5)).'@chirorg.local';
        $response = $client->request('POST', '/api/utilisateurs', [
            'json' => ['email' => $email, 'roles' => ['ROLE_USER'], 'actif' => true, 'motDePasse' => 'User-password-42!'],
        ]);
        self::assertResponseStatusCodeSame(201);
        $payload = $response->toArray();
        self::assertSame($email, $payload['email']);
        self::assertArrayNotHasKey('password', $payload);
        self::assertArrayNotHasKey('motDePasse', $payload);
        self::assertIsInt($payload['id']);
        $createdId = $payload['id'];

        $client->request('POST', '/api/utilisateurs', [
            'json' => ['email' => mb_strtoupper($email), 'motDePasse' => 'Another-password-42!'],
        ]);
        self::assertResponseStatusCodeSame(409);

        $client->request('PATCH', '/api/utilisateurs/'.$createdId, [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['actif' => false],
        ]);
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['actif' => false]);

        $client->request('PATCH', '/api/utilisateurs/'.$admin->getId(), [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['actif' => false],
        ]);
        self::assertResponseStatusCodeSame(409);

        $client->request('DELETE', '/api/utilisateurs/'.$admin->getId());
        self::assertResponseStatusCodeSame(409);

        $client->request('DELETE', '/api/utilisateurs/'.$createdId);
        self::assertResponseStatusCodeSame(204);

        $this->removeUtilisateur($admin);
    }

    public function testRoleUserCannotManageUtilisateurs(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: []);
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));

        $client->request('GET', '/api/utilisateurs');
        self::assertResponseStatusCodeSame(403);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $managed = $entityManager->find(Utilisateur::class, $utilisateur->getId());
        self::assertNotNull($managed);
        $this->removeUtilisateur($managed);
    }
}
