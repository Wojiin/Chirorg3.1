<?php

namespace App\Service;

use App\Entity\Specialite;
use App\Error\ErrorMessage;
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
            throw new ConflictHttpException(ErrorMessage::DEFAULT_SPECIALITE_PROTECTED);
        }

        $defaultSpecialite = $this->repository->findDefault()
            ?? throw new \LogicException(ErrorMessage::DEFAULT_SPECIALITE_MISSING);

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
