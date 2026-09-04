<?php

namespace App\Service;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\Materiel;
use App\Entity\Specialite;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class ReferenceDeletionGuard
{
    public function assertCanDelete(object $resource): void
    {
        $isUsed = match (true) {
            $resource instanceof Specialite => !$resource->getChirurgiens()->isEmpty()
                || !$resource->getMateriels()->isEmpty()
                || !$resource->getChirurgiesModeles()->isEmpty(),
            $resource instanceof Chirurgien => !$resource->getListesMateriel()->isEmpty(),
            $resource instanceof Materiel => !$resource->getListesMateriel()->isEmpty(),
            $resource instanceof ChirurgieModele => !$resource->getFichesTechniques()->isEmpty()
                || !$resource->getListesMateriel()->isEmpty(),
            default => false,
        };

        if ($isUsed) {
            throw new ConflictHttpException('Cette ressource ne peut pas être supprimée car elle est utilisée.');
        }
    }
}
