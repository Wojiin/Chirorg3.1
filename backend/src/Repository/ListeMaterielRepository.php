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
    // Configure le repository Doctrine chargé de la liste de matériel. Dépendances utilisées : ListeMateriel, ManagerRegistry.
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeMateriel::class);
    }

    // Recherche la liste de matériel selon one pour chirurgien and chirurgie modèle de chirurgie. Dépendances utilisées : ChirurgieModele, Chirurgien.
    public function findOneForChirurgienAndChirurgieModele(Chirurgien $chirurgien, ChirurgieModele $modele): ?ListeMateriel
    {
        return $this->findOneBy(['chirurgien' => $chirurgien, 'chirurgieModele' => $modele]);
    }
}
