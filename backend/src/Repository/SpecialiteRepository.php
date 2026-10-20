<?php

namespace App\Repository;

use App\Entity\Specialite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Specialite>
 */
class SpecialiteRepository extends ServiceEntityRepository
{
    // Configure le repository Doctrine chargé de la spécialité. Dépendances utilisées : ManagerRegistry, Specialite.
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specialite::class);
    }

    // Recherche la spécialité selon default. Dépendances utilisées : Specialite.
    public function findDefault(): ?Specialite
    {
        return $this->findOneBy(['intitule' => Specialite::SANS_SPECIALITE]);
    }
}
