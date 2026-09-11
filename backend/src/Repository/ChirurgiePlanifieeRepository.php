<?php

namespace App\Repository;

use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChirurgiePlanifiee>
 */
class ChirurgiePlanifieeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChirurgiePlanifiee::class);
    }

    /** @return list<ChirurgiePlanifiee> */
    public function findProgrammes(?\DateTimeInterface $date = null, ?string $salle = null, ?int $chirurgienId = null, ?\DateTimeInterface $dateDebut = null, ?\DateTimeInterface $dateFin = null, ?bool $valide = null, bool $withFichesTechniques = false): array
    {
        $qb = $this->baseDataQuery()
            ->orderBy('c.dateProgrammee', 'ASC')->addOrderBy('c.salle', 'ASC')->addOrderBy('c.ordre', 'ASC');
        if (null !== $date) {
            $qb->andWhere('c.dateProgrammee = :date')->setParameter('date', $date, Types::DATE_IMMUTABLE);
        } else {
            if (null !== $dateDebut) {
                $qb->andWhere('c.dateProgrammee >= :dateDebut')->setParameter('dateDebut', $dateDebut, Types::DATE_IMMUTABLE);
            }
            if (null !== $dateFin) {
                $qb->andWhere('c.dateProgrammee <= :dateFin')->setParameter('dateFin', $dateFin, Types::DATE_IMMUTABLE);
            }
        }
        if (null !== $salle) {
            $qb->andWhere('c.salle = :salle')->setParameter('salle', $salle);
        }
        if (null !== $chirurgienId) {
            $qb->andWhere('chirurgien.id = :chirurgien')->setParameter('chirurgien', $chirurgienId);
        }
        if (null !== $valide) {
            $qb->andWhere('c.valide = :valide')->setParameter('valide', $valide);
        }
        if ($withFichesTechniques) {
            $qb->leftJoin('modele.fichesTechniques', 'fiches')->addSelect('fiches');
        }

        return $qb->getQuery()->getResult();
    }

    public function findPreparationData(int $id): ?ChirurgiePlanifiee
    {
        return $this->baseDataQuery()->where('c.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }

    public function findVueFinaleData(int $id): ?ChirurgiePlanifiee
    {
        $qb = $this->baseDataQuery()->leftJoin('modele.fichesTechniques', 'fiches')->addSelect('fiches')
            ->where('c.id = :id')->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function nextOrder(\DateTimeInterface $date, string $salle, Chirurgien $chirurgien): int
    {
        $maximum = $this->createQueryBuilder('c')->select('MAX(c.ordre)')
            ->where('c.dateProgrammee = :date')->andWhere('c.salle = :salle')->andWhere('c.chirurgien = :chirurgien')
            ->setParameter('date', $date->format('Y-m-d'))->setParameter('salle', $salle)->setParameter('chirurgien', $chirurgien)
            ->getQuery()->getSingleScalarResult();

        return ((int) $maximum) + 1;
    }

    public function countProgrammeSurgeries(ChirurgiePlanifiee $chirurgie): int
    {
        return (int) $this->createQueryBuilder('programme')
            ->select('COUNT(programme.id)')
            ->where('programme.dateProgrammee = :date')
            ->andWhere('programme.salle = :salle')
            ->andWhere('programme.chirurgien = :chirurgien')
            ->setParameter('date', $chirurgie->getDateProgrammee(), Types::DATE_IMMUTABLE)
            ->setParameter('salle', $chirurgie->getSalle())
            ->setParameter('chirurgien', $chirurgie->getChirurgien())
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function baseDataQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c', 'chirurgien', 'modele', 'preparations', 'materiel', 'validePar')
            ->join('c.chirurgien', 'chirurgien')
            ->join('c.chirurgieModele', 'modele')
            ->leftJoin('c.preparationsMateriel', 'preparations')
            ->leftJoin('preparations.materiel', 'materiel')
            ->leftJoin('c.validePar', 'validePar');
    }

    //    /**
    //     * @return ChirurgiePlanifiee[] Returns an array of ChirurgiePlanifiee objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ChirurgiePlanifiee
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
