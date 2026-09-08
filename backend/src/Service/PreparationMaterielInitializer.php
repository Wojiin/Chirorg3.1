<?php

namespace App\Service;

use App\Entity\ChirurgiePlanifiee;
use App\Entity\PreparationMateriel;
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
            throw new UnprocessableEntityHttpException('Le matériel d’une chirurgie validée ne peut plus être initialisé.');
        }
        $chirurgien = $chirurgie->getChirurgien();
        $modele = $chirurgie->getChirurgieModele();
        if (null === $chirurgien || null === $modele) {
            throw new UnprocessableEntityHttpException('Le chirurgien et la chirurgie modèle sont requis.');
        }
        $liste = $this->listes->findOneForChirurgienAndChirurgieModele($chirurgien, $modele)
            ?? throw new UnprocessableEntityHttpException('Aucune liste de matériel ne correspond à ce chirurgien et à cette chirurgie modèle.');
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
