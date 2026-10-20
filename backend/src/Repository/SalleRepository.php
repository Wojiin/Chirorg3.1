<?php

namespace App\Repository;

use App\Entity\Salle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Salle> */
final class SalleRepository extends ServiceEntityRepository
{
    // Configure le repository Doctrine chargé de la salle opératoire. Dépendances utilisées : ManagerRegistry, Salle.
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }
}
