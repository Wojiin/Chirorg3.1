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
}
