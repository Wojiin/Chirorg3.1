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
        $chirurgien = $chirurgie->getChirurgien();
        $modele = $chirurgie->getChirurgieModele();
        if (null === $chirurgien || null === $modele) {
            throw new UnprocessableEntityHttpException('Le chirurgien et la chirurgie modèle sont requis.');
        }
        $liste = $this->listes->findOneForChirurgienAndChirurgieModele($chirurgien, $modele)
            ?? throw new UnprocessableEntityHttpException('Aucune liste de matériel ne correspond à ce chirurgien et à cette chirurgie modèle.');
        foreach ($liste->getMateriels() as $materiel) {
            $preparation = (new PreparationMateriel())->setMateriel($materiel);
            $chirurgie->addPreparationMateriel($preparation);
            $this->entityManager->persist($preparation);
        }
    }
}
