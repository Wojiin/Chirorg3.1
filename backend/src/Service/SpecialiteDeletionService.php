<?php

namespace App\Service;

use App\Entity\Specialite;
use App\Repository\SpecialiteRepository;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/** Réaffecte les références d'une spécialité avant sa suppression. */
final readonly class SpecialiteDeletionService
{
    public function __construct(private SpecialiteRepository $repository)
    {
    }

    public function reassignReferences(Specialite $specialite): void
    {
        if (Specialite::SANS_SPECIALITE === $specialite->getIntitule()) {
            throw new ConflictHttpException('La spécialité « Sans spécialité » ne peut pas être supprimée.');
        }

        $defaultSpecialite = $this->repository->findDefault()
            ?? throw new \LogicException('La spécialité « Sans spécialité » est absente.');

        foreach ($specialite->getChirurgiens()->toArray() as $chirurgien) {
            $chirurgien->setSpecialite($defaultSpecialite);
        }
        foreach ($specialite->getMateriels()->toArray() as $materiel) {
            $materiel->setSpecialite($defaultSpecialite);
        }
        foreach ($specialite->getChirurgiesModeles()->toArray() as $modele) {
            $modele->setSpecialite($defaultSpecialite);
        }
    }
}
