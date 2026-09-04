<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AuthenticatedApiTestCase extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    protected function createApiClient(): Client
    {
        return static::createClient([], [
            'headers' => [
                'accept' => 'application/ld+json',
                'content-type' => 'application/ld+json',
            ],
        ]);
    }

    /** @return array{Utilisateur, string} */
    protected function persistUtilisateur(bool $actif = true): array
    {
        $password = 'Test-password-42!';
        $utilisateur = (new Utilisateur())
            ->setEmail('test-'.bin2hex(random_bytes(6)).'@chirorg.local')
            ->setRoles(['ROLE_ADMIN'])
            ->setActif($actif);
        $hasher = static::getContainer()->get('test.user_password_hasher');
        self::assertInstanceOf(UserPasswordHasherInterface::class, $hasher);
        $utilisateur->setPassword($hasher->hashPassword($utilisateur, $password));

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        return [$utilisateur, $password];
    }

    protected function login(Client $client, Utilisateur $utilisateur, string $password): string
    {
        $response = $client->request('POST', '/api/auth/login', [
            'json' => ['email' => $utilisateur->getEmail(), 'password' => $password],
        ]);
        self::assertResponseIsSuccessful();
        $data = $response->toArray();
        self::assertArrayHasKey('token', $data);
        self::assertIsString($data['token']);

        return $data['token'];
    }

    protected function useBearerToken(Client $client, string $token): void
    {
        $client->setDefaultOptions([
            'headers' => [
                'accept' => 'application/ld+json',
                'content-type' => 'application/ld+json',
                'authorization' => 'Bearer '.$token,
            ],
        ]);
    }

    protected function removeUtilisateur(Utilisateur $utilisateur): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $managedUtilisateur = $entityManager->find(Utilisateur::class, $utilisateur->getId());
        if (null !== $managedUtilisateur) {
            $entityManager->remove($managedUtilisateur);
            $entityManager->flush();
        }
    }
}
