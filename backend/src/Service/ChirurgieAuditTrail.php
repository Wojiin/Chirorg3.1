<?php

namespace App\Service;

use App\Entity\ChirurgiePlanifiee;

final readonly class ChirurgieAuditTrail
{
    public function __construct(private AuthenticatedUserProvider $authenticatedUser)
    {
    }

    public function markCreated(ChirurgiePlanifiee $chirurgie): void
    {
        $actor = $this->authenticatedUser->getUser()->getUserIdentifier();
        $chirurgie->setCreePar($actor)->setModifiePar($actor);
    }

    public function markModified(ChirurgiePlanifiee $chirurgie, ?\DateTimeImmutable $at = null): void
    {
        $chirurgie
            ->setModifieLe($at ?? new \DateTimeImmutable())
            ->setModifiePar($this->authenticatedUser->getUser()->getUserIdentifier());
    }
}
