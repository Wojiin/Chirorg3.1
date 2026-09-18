<?php

namespace App\Tests\Functional\Api;

use App\Entity\Specialite;
use Doctrine\ORM\EntityManagerInterface;

/** Tests de non-régression associés aux risques OWASP applicables à l'API. */
final class SecuriteOwaspApiTest extends AuthenticatedApiTestCase
{
    public function testForgedJwtCannotAccessAProtectedResource(): void
    {
        $client = $this->createApiClient();
        $this->useBearerToken($client, 'eyJhbGciOiJSUzI1NiJ9.eyJ1c2VybmFtZSI6ImF0dGFja2VyQGV4YW1wbGUub3JnIn0.invalid');

        $client->request('GET', '/api/specialites');

        self::assertResponseStatusCodeSame(401);
    }

    public function testSqlInjectionPayloadIsHandledAsPlainFilterText(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: []);
        $specialite = (new Specialite())->setIntitule('Témoin OWASP '.bin2hex(random_bytes(5)));
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($specialite);
        $entityManager->flush();
        $specialiteId = $specialite->getId();
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));

        try {
            $response = $client->request('GET', '/api/specialites?'.http_build_query([
                'q' => "' OR 1=1 --",
                'itemsPerPage' => 100,
            ]));

            self::assertResponseIsSuccessful();
            self::assertSame(0, $response->toArray()['totalItems']);
        } finally {
            $managedSpecialite = null === $specialiteId ? null : $entityManager->find(Specialite::class, $specialiteId);
            if (null !== $managedSpecialite) {
                $entityManager->remove($managedSpecialite);
                $entityManager->flush();
            }
            $this->removeUtilisateur($utilisateur);
        }
    }

    public function testSqlInjectionPayloadCannotAuthenticate(): void
    {
        $client = $this->createApiClient();

        $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => "' OR 1=1 --",
                'password' => "' OR 1=1 --",
            ],
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    public function testUnexpectedPropertiesCannotChangeProtectedState(): void
    {
        $client = $this->createApiClient();
        [$admin, $password] = $this->persistUtilisateur();
        $this->useBearerToken($client, $this->login($client, $admin, $password));
        $label = 'Mass assignment '.bin2hex(random_bytes(5));

        try {
            $response = $client->request('POST', '/api/specialites', [
                'json' => [
                    'id' => 1,
                    'intitule' => $label,
                    'roles' => ['ROLE_ADMIN'],
                ],
            ]);

            self::assertResponseStatusCodeSame(400);
            self::assertStringNotContainsString('SQLSTATE', $response->getContent(false));
            self::assertNull(
                static::getContainer()->get(EntityManagerInterface::class)
                    ->getRepository(Specialite::class)
                    ->findOneBy(['intitule' => $label]),
            );
        } finally {
            $this->removeUtilisateur($admin);
        }
    }

    public function testOversizedInputIsRejectedWithoutBeingPersisted(): void
    {
        $client = $this->createApiClient();
        [$admin, $password] = $this->persistUtilisateur();
        $this->useBearerToken($client, $this->login($client, $admin, $password));
        $payload = str_repeat('A', 101);

        try {
            $client->request('POST', '/api/specialites', ['json' => ['intitule' => $payload]]);

            self::assertResponseStatusCodeSame(422);
            self::assertNull(
                static::getContainer()->get(EntityManagerInterface::class)
                    ->getRepository(Specialite::class)
                    ->findOneBy(['intitule' => $payload]),
            );
        } finally {
            $this->removeUtilisateur($admin);
        }
    }

    public function testRoleUserCannotUploadTechnicalSheetImages(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: []);
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));

        try {
            $client->request('POST', '/api/fiche-technique-images');

            self::assertResponseStatusCodeSame(403);
        } finally {
            $this->removeUtilisateur($utilisateur);
        }
    }
}
