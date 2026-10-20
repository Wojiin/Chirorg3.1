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
    // Configure le repository Doctrine chargé de la chirurgie planifiée. Dépendances utilisées : ChirurgiePlanifiee, ManagerRegistry.
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChirurgiePlanifiee::class);
    }

    // Recherche la chirurgie planifiée selon programmes. Dépendances utilisées : Types.
    /** @return list<ChirurgiePlanifiee> */
    public function findProgrammes(?\DateTimeInterface $date = null, ?string $salle = null, ?int $chirurgienId = null, ?\DateTimeInterface $dateDebut = null, ?\DateTimeInterface $dateFin = null): array
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

        return $qb->getQuery()->getResult();
    }

    // Charge une chirurgie planifiée avec son chirurgien, son modèle et chaque préparation de matériel nécessaire à l'écran de préparation. Dépendances utilisées : QueryBuilder.
    public function findPreparationData(int $id): ?ChirurgiePlanifiee
    {
        return $this->baseDataQuery()->where('c.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }

    // Charge une chirurgie validée avec sa préparation de matériel et les fiches techniques du modèle pour la vue finale. Dépendances utilisées : QueryBuilder.
    public function findVueFinaleData(int $id): ?ChirurgiePlanifiee
    {
        $qb = $this->baseDataQuery()->leftJoin('modele.fichesTechniques', 'fiches')->addSelect('fiches')
            ->where('c.id = :id')->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    // Retourne le prochain ordre disponible dans le programme opératoire. Dépendances utilisées : Chirurgien.
    public function nextOrder(\DateTimeInterface $date, string $salle, Chirurgien $chirurgien): int
    {
        $maximum = $this->createQueryBuilder('c')->select('MAX(c.ordre)')
            ->where('c.dateProgrammee = :date')->andWhere('c.salle = :salle')->andWhere('c.chirurgien = :chirurgien')
            ->setParameter('date', $date->format('Y-m-d'))->setParameter('salle', $salle)->setParameter('chirurgien', $chirurgien)
            ->getQuery()->getSingleScalarResult();

        return ((int) $maximum) + 1;
    }

    // Compte les chirurgies d'un programme opératoire. Dépendances utilisées : ChirurgiePlanifiee, Types.
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

    // Supprime les programmes opératoires antérieurs à la date de conservation. Dépendances utilisées : Types.
    /** Supprime toutes les chirurgies appartenant aux programmes antérieurs à la date limite. */
    public function deleteProgrammesBefore(\DateTimeInterface $dateLimite): int
    {
        return $this->createQueryBuilder('chirurgie')
            ->delete()
            ->where('chirurgie.dateProgrammee < :dateLimite')
            ->setParameter('dateLimite', \DateTimeImmutable::createFromInterface($dateLimite), Types::DATE_IMMUTABLE)
            ->getQuery()
            ->execute();
    }

    // Répercute le nouvel intitulé de salle sur les chirurgies planifiées. Dépendances utilisées : aucune dépendance de service directe.
    /** Répercute le renommage d'une salle sur tous les programmes associés. */
    public function renameSalle(string $ancienIntitule, string $nouvelIntitule): int
    {
        return $this->createQueryBuilder('chirurgie')
            ->update()
            ->set('chirurgie.salle', ':nouvelIntitule')
            ->where('chirurgie.salle = :ancienIntitule')
            ->setParameter('nouvelIntitule', $nouvelIntitule)
            ->setParameter('ancienIntitule', $ancienIntitule)
            ->getQuery()
            ->execute();
    }

    // Construit la requête Doctrine commune aux lectures de chirurgie. Dépendances utilisées : aucune dépendance de service directe.
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
}
