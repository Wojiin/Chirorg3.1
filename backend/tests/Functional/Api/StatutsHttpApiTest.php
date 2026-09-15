<?php

namespace App\Tests\Functional\Api;

final class StatutsHttpApiTest extends AuthenticatedApiTestCase
{
    /** @return iterable<string, array{string, array<string, mixed>}> */
    public static function protectedResources(): iterable
    {
        yield 'salles' => ['/api/salles', []];
        yield 'spécialités' => ['/api/specialites', []];
        yield 'chirurgiens' => ['/api/chirurgiens', []];
        yield 'chirurgies modèles' => ['/api/chirurgie-modeles', []];
        yield 'matériels' => ['/api/materiels', []];
        yield 'fiches techniques' => ['/api/fiches-techniques', []];
        yield 'listes de matériel' => ['/api/listes-materiel', []];
        yield 'utilisateurs' => ['/api/utilisateurs', [
            'email' => 'invalid-email',
            'motDePasse' => 'faible',
        ]];
    }

    /** @param array<string, mixed> $invalidPayload */
    #[\PHPUnit\Framework\Attributes\DataProvider('protectedResources')]
    public function testAnonymousRequestsReturn401(string $collection, array $invalidPayload): void
    {
        $client = $this->createApiClient();
        $client->request('GET', $collection);
        self::assertResponseStatusCodeSame(401);

        $client->request('POST', $collection, ['json' => $invalidPayload]);
        self::assertResponseStatusCodeSame(401);
    }

    /** @param array<string, mixed> $invalidPayload */
    #[\PHPUnit\Framework\Attributes\DataProvider('protectedResources')]
    public function testRoleUserCanOnlyReadBusinessReferences(string $collection, array $invalidPayload): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: []);
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));

        $client->request('GET', $collection);
        self::assertResponseStatusCodeSame('/api/utilisateurs' === $collection ? 403 : 200);

        $client->request('POST', $collection, ['json' => $invalidPayload]);
        self::assertResponseStatusCodeSame(403);
        $this->removeUtilisateur($utilisateur);
    }

    /** @param array<string, mixed> $invalidPayload */
    #[\PHPUnit\Framework\Attributes\DataProvider('protectedResources')]
    public function testAdminReceives400ForMalformedJsonAnd422ForInvalidData(string $collection, array $invalidPayload): void
    {
        $client = $this->createApiClient();
        [$admin, $password] = $this->persistUtilisateur();
        $this->useBearerToken($client, $this->login($client, $admin, $password));

        $client->request('POST', $collection, [
            'headers' => ['content-type' => 'application/ld+json'],
            'body' => '{invalid',
        ]);
        self::assertResponseStatusCodeSame(400);

        $client->request('POST', $collection, ['json' => $invalidPayload]);
        self::assertResponseStatusCodeSame(422);
        $this->removeUtilisateur($admin);
    }

    /** @param array<string, mixed> $_invalidPayload */
    #[\PHPUnit\Framework\Attributes\DataProvider('protectedResources')]
    public function testAdminReceives404ForUnknownItems(string $collection, array $_invalidPayload): void
    {
        $client = $this->createApiClient();
        [$admin, $password] = $this->persistUtilisateur();
        $this->useBearerToken($client, $this->login($client, $admin, $password));

        $client->request('GET', $collection.'/2147483647');
        self::assertResponseStatusCodeSame(404);
        $this->removeUtilisateur($admin);
    }
}
