<?php

namespace App\Service;

use App\Entity\ChirurgiePlanifiee;

final readonly class ChirurgieAuditTrail
{
    // Injecte les services nécessaires au traitement de la chirurgie planifiée. Dépendances utilisées : AuthenticatedUserProvider.
    public function __construct(private AuthenticatedUserProvider $authenticatedUser)
    {
    }

    // Renseigne l'auteur et la date de création de la chirurgie. Dépendances utilisées : AuthenticatedUserProvider, ChirurgiePlanifiee.
    public function markCreated(ChirurgiePlanifiee $chirurgie): void
    {
        $actor = $this->authenticatedUser->getUser()->getUserIdentifier();
        $chirurgie->setCreePar($actor)->setModifiePar($actor);
    }

    // Renseigne l'auteur et la date de modification de la chirurgie. Dépendances utilisées : AuthenticatedUserProvider, ChirurgiePlanifiee.
    public function markModified(ChirurgiePlanifiee $chirurgie, ?\DateTimeImmutable $at = null): void
    {
        $chirurgie
            ->setModifieLe($at ?? new \DateTimeImmutable())
            ->setModifiePar($this->authenticatedUser->getUser()->getUserIdentifier());
    }
}
