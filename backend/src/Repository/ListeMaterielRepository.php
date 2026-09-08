<?php

namespace App\Repository;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ListeMateriel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeMateriel>
 */
class ListeMaterielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeMateriel::class);
    }

    public function findOneForChirurgienAndChirurgieModele(Chirurgien $chirurgien, ChirurgieModele $modele): ?ListeMateriel
    {
        return $this->findOneBy(['chirurgien' => $chirurgien, 'chirurgieModele' => $modele]);
    }

    //    /**
    //     * @return ListeMateriel[] Returns an array of ListeMateriel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ListeMateriel
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
