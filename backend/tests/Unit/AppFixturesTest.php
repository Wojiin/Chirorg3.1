<?php

namespace App\Tests\Unit;

use App\DataFixtures\AppFixtures;
use App\Entity\Utilisateur;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixturesTest extends TestCase
{
    public function testFixturesCreateAnActiveAdministratorWithAHashedPassword(): void
    {
        $persisted = [];
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher
            ->expects(self::once())
            ->method('hashPassword')
            ->with(self::isInstanceOf(Utilisateur::class), AppFixtures::ADMIN_PASSWORD)
            ->willReturn('hashed-fixture-password');

        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->expects(self::exactly(19))
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persisted): void {
                $persisted[] = $entity;
            });
        $manager->expects(self::once())->method('flush');

        (new AppFixtures($hasher))->load($manager);

        $administrators = array_values(array_filter(
            $persisted,
            static fn (object $entity): bool => $entity instanceof Utilisateur,
        ));
        self::assertCount(1, $administrators);
        $administrator = $administrators[0];
        self::assertInstanceOf(Utilisateur::class, $administrator);
        self::assertSame(AppFixtures::ADMIN_EMAIL, $administrator->getEmail());
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $administrator->getRoles());
        self::assertSame('hashed-fixture-password', $administrator->getPassword());
        self::assertTrue($administrator->isActif());
    }
}
