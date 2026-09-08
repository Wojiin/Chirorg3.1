<?php

namespace App\Tests\Functional\Api;

use App\Entity\Specialite;
use Doctrine\ORM\EntityManagerInterface;

final class AutorisationApiTest extends AuthenticatedApiTestCase
{
    public function testAnonymousUserCannotReadProtectedResources(): void
    {
        $client = $this->createApiClient();

        $client->request('GET', '/api/specialites');

        self::assertResponseStatusCodeSame(401);

        $client->request('GET', '/api/docs');
        self::assertResponseIsSuccessful();

        $client->request('HEAD', '/api/docs');
        self::assertResponseIsSuccessful();

        $client->request('GET', '/api/contexts/Specialite');
        self::assertResponseIsSuccessful();
    }

    public function testCorsPreflightRemainsPublic(): void
    {
        $client = $this->createApiClient();

        $client->request('OPTIONS', '/api/specialites', [
            'headers' => [
                'origin' => 'http://localhost:5173',
                'access-control-request-method' => 'POST',
            ],
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testRoleUserCanReadButCannotWrite(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: []);
        $specialite = (new Specialite())->setIntitule('Protégée '.bin2hex(random_bytes(5)));
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($specialite);
        $entityManager->flush();
        $specialiteId = $specialite->getId();
        self::assertNotNull($specialiteId);
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));

        $client->request('GET', '/api/specialites');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/api/specialites', [
            'json' => ['intitule' => 'Interdite '.bin2hex(random_bytes(5))],
        ]);
        self::assertResponseStatusCodeSame(403);

        $client->request('PATCH', '/api/specialites/'.$specialiteId, [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['intitule' => 'Modification interdite'],
        ]);
        self::assertResponseStatusCodeSame(403);

        $client->request('DELETE', '/api/specialites/'.$specialiteId);
        self::assertResponseStatusCodeSame(403);

        $client->request('POST', '/api/auth/logout');
        self::assertResponseIsSuccessful();
        $managedSpecialite = $entityManager->find(Specialite::class, $specialiteId);
        if (null !== $managedSpecialite) {
            $entityManager->remove($managedSpecialite);
            $entityManager->flush();
        }
        $this->removeUtilisateur($utilisateur);
    }

    public function testRoleAdminInheritsReadAccessAndCanWrite(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur();
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));

        $client->request('GET', '/api/specialites');
        self::assertResponseIsSuccessful();

        $response = $client->request('POST', '/api/specialites', [
            'json' => ['intitule' => 'Administration '.bin2hex(random_bytes(5))],
        ]);
        self::assertResponseStatusCodeSame(201);
        $data = $response->toArray();
        self::assertArrayHasKey('@id', $data);
        self::assertIsString($data['@id']);

        $client->request('DELETE', $data['@id']);
        self::assertResponseStatusCodeSame(204);

        $client->request('POST', '/api/auth/logout');
        self::assertResponseIsSuccessful();
        $this->removeUtilisateur($utilisateur);
    }
}
