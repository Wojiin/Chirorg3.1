<?php

namespace App\Tests\Unit;

use App\DataFixtures\AppFixtures;
use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use App\Entity\FicheTechnique;
use App\Entity\ListeMateriel;
use App\Entity\Materiel;
use App\Entity\PreparationMateriel;
use App\Entity\Salle;
use App\Entity\Specialite;
use App\Entity\Utilisateur;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixturesTest extends TestCase
{
    /** @var list<object> */
    private array $entities = [];

    protected function setUp(): void
    {
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher
            ->expects(self::exactly(2))
            ->method('hashPassword')
            ->willReturnCallback(static fn (Utilisateur $user, string $password): string => 'hashed-'.hash('sha256', $user->getEmail().$password));

        $manager = $this->createMock(ObjectManager::class);
        $manager
            ->method('persist')
            ->willReturnCallback(function (object $entity): void {
                $this->entities[] = $entity;
            });
        $manager->expects(self::once())->method('flush');

        (new AppFixtures($hasher))->load($manager);
    }

    public function testFixturesCoverEveryEntityWithProductionLikeVolumes(): void
    {
        self::assertCount(2, $this->entitiesOfType(Utilisateur::class));
        self::assertCount(3, $this->entitiesOfType(Salle::class));
        self::assertCount(6, $this->entitiesOfType(Specialite::class));
        self::assertCount(25, $this->entitiesOfType(Chirurgien::class));
        self::assertCount(20, $this->entitiesOfType(ChirurgieModele::class));
        self::assertCount(60, $this->entitiesOfType(FicheTechnique::class));
        self::assertCount(100, $this->entitiesOfType(Materiel::class));
        self::assertCount(100, $this->entitiesOfType(ListeMateriel::class));
        self::assertCount(30, $this->entitiesOfType(ChirurgiePlanifiee::class));
        self::assertGreaterThan(300, count($this->entitiesOfType(PreparationMateriel::class)));
        self::assertGreaterThan(600, count($this->entities));
    }

    public function testFixtureAccountsAreActiveAndPasswordsAreHashed(): void
    {
        $users = [];
        foreach ($this->entitiesOfType(Utilisateur::class) as $user) {
            $users[$user->getEmail()] = $user;
        }

        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $users[AppFixtures::ADMIN_EMAIL]->getRoles());
        self::assertSame(['ROLE_USER'], $users[AppFixtures::USER_EMAIL]->getRoles());
        self::assertStringStartsWith('hashed-', $users[AppFixtures::ADMIN_EMAIL]->getPassword());
        self::assertStringStartsWith('hashed-', $users[AppFixtures::USER_EMAIL]->getPassword());
        self::assertTrue($users[AppFixtures::ADMIN_EMAIL]->isActif());
        self::assertTrue($users[AppFixtures::USER_EMAIL]->isActif());
    }

    public function testEveryReferenceAssociationUsesTheSameSpeciality(): void
    {
        foreach ($this->entitiesOfType(Chirurgien::class) as $chirurgien) {
            self::assertInstanceOf(Specialite::class, $chirurgien->getSpecialite());
        }
        foreach ($this->entitiesOfType(ChirurgieModele::class) as $modele) {
            self::assertInstanceOf(Specialite::class, $modele->getSpecialite());
        }
        foreach ($this->entitiesOfType(FicheTechnique::class) as $fiche) {
            self::assertInstanceOf(ChirurgieModele::class, $fiche->getChirurgieModele());
            self::assertContains($fiche->getOrdre(), [1, 2, 3]);
        }
        foreach ($this->entitiesOfType(ListeMateriel::class) as $liste) {
            $specialite = $liste->getChirurgien()?->getSpecialite();
            self::assertSame($specialite, $liste->getChirurgieModele()?->getSpecialite());
            self::assertGreaterThanOrEqual(10, $liste->getMateriels()->count());
            self::assertLessThanOrEqual(15, $liste->getMateriels()->count());
            foreach ($liste->getMateriels() as $materiel) {
                self::assertSame($specialite, $materiel->getSpecialite());
            }
        }
    }

    public function testPlannedSurgeriesExposeVariedPreparationStates(): void
    {
        $chirurgies = $this->entitiesOfType(ChirurgiePlanifiee::class);
        self::assertCount(6, array_filter($chirurgies, static fn (ChirurgiePlanifiee $item): bool => $item->isValide()));
        self::assertSame(['Salle A', 'Salle A', 'Salle B', 'Salle B'], array_slice(array_map(
            static fn (ChirurgiePlanifiee $item): ?string => $item->getSalle(),
            $chirurgies,
        ), 0, 4));
        foreach ($chirurgies as $chirurgie) {
            self::assertNotNull($chirurgie->getChirurgien());
            self::assertNotNull($chirurgie->getChirurgieModele());
            self::assertGreaterThanOrEqual(10, $chirurgie->getPreparationsMateriel()->count());
        }
        self::assertTrue($chirurgies[0]->getPreparationsMateriel()->forAll(
            static fn (int $index, PreparationMateriel $item): bool => $item->isCoche() && null !== $item->getCochePar(),
        ));
        self::assertFalse($chirurgies[6]->isValide());
    }

    /** @template T of object
     *
     * @param class-string<T> $className
     *
     * @return list<T>
     */
    private function entitiesOfType(string $className): array
    {
        return array_values(array_filter(
            $this->entities,
            static fn (object $entity): bool => $entity instanceof $className,
        ));
    }
}
