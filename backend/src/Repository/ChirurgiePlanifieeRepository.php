<?php

namespace App\Repository;

use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function findProgrammes(?\DateTimeInterface $date = null, ?string $salle = null, ?int $chirurgienId = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('chirurgien', 'modele', 'preparations', 'materiel')
            ->join('c.chirurgien', 'chirurgien')
            ->join('c.chirurgieModele', 'modele')
            ->leftJoin('c.preparationsMateriel', 'preparations')
            ->leftJoin('preparations.materiel', 'materiel')
            ->orderBy('c.dateProgrammee', 'ASC')->addOrderBy('c.salle', 'ASC')->addOrderBy('c.ordre', 'ASC');
        if (null !== $date) {
            $qb->andWhere('c.dateProgrammee = :date')->setParameter('date', $date->format('Y-m-d'));
        }
        if (null !== $salle) {
            $qb->andWhere('c.salle = :salle')->setParameter('salle', $salle);
        }
        if (null !== $chirurgienId) {
            $qb->andWhere('chirurgien.id = :chirurgien')->setParameter('chirurgien', $chirurgienId);
        }

        return $qb->getQuery()->getResult();
    }

    public function nextOrder(\DateTimeInterface $date, string $salle, Chirurgien $chirurgien): int
    {
        $maximum = $this->createQueryBuilder('c')->select('MAX(c.ordre)')
            ->where('c.dateProgrammee = :date')->andWhere('c.salle = :salle')->andWhere('c.chirurgien = :chirurgien')
            ->setParameter('date', $date->format('Y-m-d'))->setParameter('salle', $salle)->setParameter('chirurgien', $chirurgien)
            ->getQuery()->getSingleScalarResult();

        return ((int) $maximum) + 1;
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
