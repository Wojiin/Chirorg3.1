<?php

namespace App\Service;

use App\Entity\ChirurgiePlanifiee;
use App\Error\ErrorMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class ChirurgieValidationService
{
    // Injecte les services nécessaires au traitement de la chirurgie planifiée. Dépendances utilisées : AuthenticatedUserProvider, ChirurgieAuditTrail, EntityManagerInterface.
    public function __construct(private AuthenticatedUserProvider $authenticatedUser, private ChirurgieAuditTrail $auditTrail, private EntityManagerInterface $entityManager)
    {
    }

    // Valide la chirurgie planifiée selon les règles métier. Dépendances utilisées : AuthenticatedUserProvider, ChirurgieAuditTrail, ChirurgiePlanifiee, EntityManagerInterface, ErrorMessage, UnprocessableEntityHttpException.
    public function validate(ChirurgiePlanifiee $chirurgie): ChirurgiePlanifiee
    {
        if ($chirurgie->isValide()) {
            return $chirurgie;
        }
        if ($chirurgie->getPreparationsMateriel()->isEmpty()) {
            throw new UnprocessableEntityHttpException(ErrorMessage::VALIDATION_REQUIRES_PREPARATION);
        }
        foreach ($chirurgie->getPreparationsMateriel() as $preparation) {
            if (!$preparation->isCoche() && !$preparation->isAbsent()) {
                throw new UnprocessableEntityHttpException(ErrorMessage::VALIDATION_REQUIRES_ALL_MATERIALS);
            }
        }
        foreach ($chirurgie->getPreparationsMateriel() as $preparation) {
            if ($preparation->isAbsent()) {
                $this->auditTrail->markModified($chirurgie);
                $this->entityManager->flush();

                return $chirurgie;
            }
        }

        $now = new \DateTimeImmutable();
        $chirurgie->setValide(true)->setValideLe($now)->setValidePar($this->authenticatedUser->getUser());
        $this->auditTrail->markModified($chirurgie, $now);
        $this->entityManager->flush();

        return $chirurgie;
    }
}
