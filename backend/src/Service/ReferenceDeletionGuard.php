<?php

namespace App\Service;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use App\Entity\ListeMateriel;
use App\Entity\Materiel;
use App\Entity\Utilisateur;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class ReferenceDeletionGuard
{
    public function assertCanDelete(object $resource): void
    {
        $isUsed = match (true) {
            $resource instanceof Chirurgien => !$resource->getListesMateriel()->isEmpty()
                || !$resource->getChirurgiesPlanifiees()->isEmpty(),
            $resource instanceof Materiel => !$resource->getListesMateriel()->isEmpty()
                || !$resource->getPreparationsMateriel()->isEmpty(),
            $resource instanceof ChirurgieModele => !$resource->getFichesTechniques()->isEmpty()
                || !$resource->getListesMateriel()->isEmpty()
                || !$resource->getChirurgiesPlanifiees()->isEmpty(),
            $resource instanceof ListeMateriel => $this->isListeUsed($resource),
            $resource instanceof ChirurgiePlanifiee => $resource->isValide(),
            $resource instanceof Utilisateur => !$resource->getChirurgiesValidees()->isEmpty()
                || !$resource->getPreparationsCochees()->isEmpty(),
            default => false,
        };

        if ($isUsed) {
            throw new ConflictHttpException('Cette ressource ne peut pas être supprimée car elle est utilisée.');
        }
    }

    private function isListeUsed(ListeMateriel $liste): bool
    {
        $chirurgien = $liste->getChirurgien();
        if (null === $chirurgien) {
            return false;
        }

        foreach ($chirurgien->getChirurgiesPlanifiees() as $chirurgie) {
            if ($chirurgie->getChirurgieModele() === $liste->getChirurgieModele()) {
                return true;
            }
        }

        return false;
    }
}
