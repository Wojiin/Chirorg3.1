<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

final class UtilisateurTest extends TestCase
{
    public function testItNormalizesEmailAndAlwaysHasUserRole(): void
    {
        $utilisateur = (new Utilisateur())
            ->setEmail('  ADMIN@Example.COM ')
            ->setRoles(['ROLE_ADMIN']);

        self::assertSame('admin@example.com', $utilisateur->getEmail());
        self::assertSame('admin@example.com', $utilisateur->getUserIdentifier());
        self::assertEqualsCanonicalizing(['ROLE_ADMIN', 'ROLE_USER'], $utilisateur->getRoles());
        self::assertTrue($utilisateur->isActif());
    }

    public function testItInitializesAuditDates(): void
    {
        $utilisateur = new Utilisateur();

        self::assertInstanceOf(\DateTimeImmutable::class, $utilisateur->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $utilisateur->getUpdatedAt());
    }

    public function testItRejectsAnEmptyAuthenticatedIdentifier(): void
    {
        $this->expectException(\LogicException::class);

        (new Utilisateur())->getUserIdentifier();
    }

    public function testSerializedRepresentationDoesNotExposePasswordHash(): void
    {
        $utilisateur = (new Utilisateur())->setPassword('$2y$hashed-password');

        self::assertStringNotContainsString('$2y$hashed-password', serialize($utilisateur));
    }
}
