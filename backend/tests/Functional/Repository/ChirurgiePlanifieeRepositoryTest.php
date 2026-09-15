<?php

namespace App\Tests\Functional\Repository;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use App\Entity\Specialite;
use App\Repository\ChirurgiePlanifieeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ChirurgiePlanifieeRepositoryTest extends KernelTestCase
{
    public function testDeleteProgrammesBeforePreservesTheBoundaryDate(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $specialite = (new Specialite())->setIntitule('Purge '.bin2hex(random_bytes(4)));
            $chirurgien = (new Chirurgien())->setPrenom('Test')->setNom('Purge')->setSpecialite($specialite);
            $modele = (new ChirurgieModele())->setIntitule('Intervention purge')->setSpecialite($specialite);
            $expired = (new ChirurgiePlanifiee())
                ->setDateProgrammee(new \DateTimeImmutable('2030-01-14'))
                ->setSalle('Salle purge')
                ->setOrdre(1)
                ->setChirurgien($chirurgien)
                ->setChirurgieModele($modele);
            $current = (new ChirurgiePlanifiee())
                ->setDateProgrammee(new \DateTimeImmutable('2030-01-15'))
                ->setSalle('Salle purge')
                ->setOrdre(1)
                ->setChirurgien($chirurgien)
                ->setChirurgieModele($modele);
            foreach ([$specialite, $chirurgien, $modele, $expired, $current] as $entity) {
                $entityManager->persist($entity);
            }
            $entityManager->flush();
            $expiredId = $expired->getId();
            $currentId = $current->getId();

            $repository = self::getContainer()->get(ChirurgiePlanifieeRepository::class);
            self::assertGreaterThanOrEqual(1, $repository->deleteProgrammesBefore(new \DateTimeImmutable('2030-01-15')));

            $entityManager->clear();
            self::assertNull($entityManager->find(ChirurgiePlanifiee::class, $expiredId));
            self::assertNotNull($entityManager->find(ChirurgiePlanifiee::class, $currentId));
        } finally {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
        }
    }
}
