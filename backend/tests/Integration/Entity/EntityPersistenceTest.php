<?php

namespace App\Tests\Integration\Entity;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\FicheTechnique;
use App\Entity\ListeMateriel;
use App\Entity\Salle;
use App\Entity\Specialite;
use App\Entity\Utilisateur;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class EntityPersistenceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        parent::tearDown();
    }

    public function testSpecialiteLabelIsUniqueInDatabase(): void
    {
        $intitule = 'Orthopédie '.$this->suffix();
        $this->entityManager->persist((new Specialite())->setIntitule($intitule));
        $this->entityManager->flush();
        $this->entityManager->persist((new Specialite())->setIntitule($intitule));

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entityManager->flush();
    }

    public function testSalleLabelIsUniqueInDatabase(): void
    {
        $intitule = 'Salle '.strtoupper($this->suffix());
        $this->entityManager->persist((new Salle())->setIntitule($intitule));
        $this->entityManager->flush();
        $this->entityManager->persist((new Salle())->setIntitule($intitule));

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entityManager->flush();
    }

    public function testChirurgieModeleIsUniqueForASpecialite(): void
    {
        $specialite = (new Specialite())->setIntitule('Cardiologie '.$this->suffix());
        $this->entityManager->persist($specialite);
        $this->entityManager->persist((new ChirurgieModele())->setIntitule('Pontage')->setSpecialite($specialite));
        $this->entityManager->flush();
        $this->entityManager->persist((new ChirurgieModele())->setIntitule('Pontage')->setSpecialite($specialite));

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entityManager->flush();
    }

    public function testListeMaterielIsUniqueForAChirurgienAndChirurgieModele(): void
    {
        $specialite = (new Specialite())->setIntitule('Urologie '.$this->suffix());
        $chirurgien = (new Chirurgien())->setPrenom('Claire')->setNom('Martin')->setSpecialite($specialite);
        $chirurgieModele = (new ChirurgieModele())->setIntitule('Néphrectomie')->setSpecialite($specialite);
        $this->entityManager->persist($specialite);
        $this->entityManager->persist($chirurgien);
        $this->entityManager->persist($chirurgieModele);
        $this->entityManager->persist(
            (new ListeMateriel())
                ->setIntitule('Liste principale')
                ->setChirurgien($chirurgien)
                ->setChirurgieModele($chirurgieModele),
        );
        $this->entityManager->flush();
        $this->entityManager->persist(
            (new ListeMateriel())
                ->setIntitule('Liste secondaire')
                ->setChirurgien($chirurgien)
                ->setChirurgieModele($chirurgieModele),
        );

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entityManager->flush();
    }

    public function testUtilisateurEmailIsUniqueInDatabase(): void
    {
        $this->entityManager->persist($this->utilisateur('unique@example.com'));
        $this->entityManager->flush();
        $this->entityManager->persist($this->utilisateur('unique@example.com'));

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entityManager->flush();
    }

    public function testUtilisateurUpdatedAtChangesOnUpdate(): void
    {
        $oldDate = new \DateTimeImmutable('-1 day');
        $utilisateur = $this->utilisateur('lifecycle@example.com')->setUpdatedAt($oldDate);
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $utilisateur->setActif(false);
        $this->entityManager->flush();

        self::assertGreaterThan($oldDate, $utilisateur->getUpdatedAt());
    }

    public function testRemovingFicheTechniqueFromModeleDeletesTheOrphan(): void
    {
        $specialite = (new Specialite())->setIntitule('Cardiologie '.$this->suffix());
        $chirurgieModele = (new ChirurgieModele())->setIntitule('Pontage')->setSpecialite($specialite);
        $fiche = (new FicheTechnique())
            ->setTitre('Installation')
            ->setDescription('Positionner le patient')
            ->setOrdre(0);
        $chirurgieModele->addFicheTechnique($fiche);
        $this->entityManager->persist($specialite);
        $this->entityManager->persist($chirurgieModele);
        $this->entityManager->persist($fiche);
        $this->entityManager->flush();
        $ficheId = $fiche->getId();
        self::assertNotNull($ficheId);

        $chirurgieModele->removeFicheTechnique($fiche);
        $this->entityManager->flush();

        self::assertNull($this->entityManager->find(FicheTechnique::class, $ficheId));
    }

    private function utilisateur(string $email): Utilisateur
    {
        return (new Utilisateur())
            ->setEmail($email)
            ->setPassword('test-password-hash');
    }

    private function suffix(): string
    {
        return bin2hex(random_bytes(5));
    }
}
