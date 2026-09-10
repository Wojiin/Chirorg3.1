<?php

namespace App\Service;

use App\Entity\ChirurgiePlanifiee;
use App\Entity\PreparationMateriel;
use App\Error\ErrorMessage;
use App\Repository\ListeMaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class PreparationMaterielInitializer
{
    public function __construct(private ListeMaterielRepository $listes, private EntityManagerInterface $entityManager)
    {
    }

    public function initialize(ChirurgiePlanifiee $chirurgie): void
    {
        if ($chirurgie->isValide()) {
            throw new UnprocessableEntityHttpException(ErrorMessage::VALIDATED_CHIRURGIE_INITIALIZATION_FORBIDDEN);
        }
        $chirurgien = $chirurgie->getChirurgien();
        $modele = $chirurgie->getChirurgieModele();
        if (null === $chirurgien || null === $modele) {
            throw new UnprocessableEntityHttpException(ErrorMessage::CHIRURGIEN_AND_MODELE_REQUIRED);
        }
        $liste = $this->listes->findOneForChirurgienAndChirurgieModele($chirurgien, $modele)
            ?? throw new UnprocessableEntityHttpException(ErrorMessage::LISTE_MATERIEL_NOT_FOUND);
        $existants = [];
        foreach ($chirurgie->getPreparationsMateriel() as $preparation) {
            $id = $preparation->getMateriel()?->getId();
            if (null !== $id) {
                $existants[$id] = true;
            }
        }
        foreach ($liste->getMateriels() as $materiel) {
            if (null !== $materiel->getId() && isset($existants[$materiel->getId()])) {
                continue;
            }
            $preparation = (new PreparationMateriel())->setMateriel($materiel);
            $chirurgie->addPreparationMateriel($preparation);
            $this->entityManager->persist($preparation);
        }
    }
}
