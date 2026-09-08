<?php

namespace App\Tests\Functional\Api;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AuthentificationApiTest extends AuthenticatedApiTestCase
{
    public function testJwtLifecycleWithPersistentRotatingRefreshToken(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur();

        $client->request('POST', '/api/auth/login', [
            'json' => ['email' => $utilisateur->getEmail(), 'password' => 'wrong-password'],
        ]);
        self::assertResponseStatusCodeSame(401);

        $accessToken = $this->login($client, $utilisateur, $password);
        $loginCookie = $client->getCookieJar()->get('refresh_token', '/api/auth/refresh');
        self::assertNotNull($loginCookie);
        self::assertTrue($loginCookie->isHttpOnly());
        $firstRefreshToken = $loginCookie->getValue();

        $this->useBearerToken($client, $accessToken);
        $client->request('GET', '/api/specialites');
        self::assertResponseIsSuccessful();

        $refreshResponse = $client->request('POST', '/api/auth/refresh');
        self::assertResponseIsSuccessful();
        $refreshData = $refreshResponse->toArray();
        self::assertArrayHasKey('token', $refreshData);
        self::assertIsString($refreshData['token']);
        self::assertArrayNotHasKey('refresh_token', $refreshData);

        $rotatedCookie = $client->getCookieJar()->get('refresh_token', '/api/auth/refresh');
        self::assertNotNull($rotatedCookie);
        self::assertNotSame($firstRefreshToken, $rotatedCookie->getValue());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = $entityManager->getRepository(RefreshToken::class);
        self::assertInstanceOf(RefreshTokenRepository::class, $repository);
        self::assertNull($repository->findOneBy(['refreshToken' => $firstRefreshToken]));
        self::assertInstanceOf(RefreshToken::class, $repository->findOneBy(['refreshToken' => $rotatedCookie->getValue()]));

        $client->setDefaultOptions([
            'headers' => [
                'accept' => 'application/ld+json',
                'content-type' => 'application/ld+json',
            ],
        ]);
        $client->request('POST', '/api/auth/logout');
        self::assertResponseIsSuccessful();
        self::assertNull($client->getCookieJar()->get('refresh_token', '/api/auth/refresh'));
        self::assertNull($repository->findOneBy(['refreshToken' => $rotatedCookie->getValue()]));

        $client->request('POST', '/api/auth/refresh');
        self::assertResponseStatusCodeSame(401);

        $this->removeUtilisateur($utilisateur);
    }

    public function testDisabledUtilisateurCannotAuthenticate(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(false);

        $client->request('POST', '/api/auth/login', [
            'json' => ['email' => $utilisateur->getEmail(), 'password' => $password],
        ]);
        self::assertResponseStatusCodeSame(401);

        $this->removeUtilisateur($utilisateur);
    }
}
