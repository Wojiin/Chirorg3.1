<?php

namespace App\Tests\Unit\Entity;

use App\Entity\RefreshToken;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

final class RefreshTokenTest extends TestCase
{
    public function testBundleModelCreatesAValidPersistentToken(): void
    {
        $utilisateur = (new Utilisateur())->setEmail('user@example.com');
        $refreshToken = RefreshToken::createForUserWithTtl('test-refresh-token', $utilisateur, 60);

        self::assertSame('test-refresh-token', $refreshToken->getRefreshToken());
        self::assertSame('user@example.com', $refreshToken->getUsername());
        self::assertTrue($refreshToken->isValid());
    }
}
