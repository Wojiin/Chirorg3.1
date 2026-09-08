<?php

namespace App\Service;

use App\Entity\ChirurgiePlanifiee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class ChirurgieValidationService
{
    public function __construct(private AuthenticatedUserProvider $authenticatedUser, private ChirurgieAuditTrail $auditTrail, private EntityManagerInterface $entityManager)
    {
    }

    public function validate(ChirurgiePlanifiee $chirurgie): ChirurgiePlanifiee
    {
        if ($chirurgie->isValide()) {
            return $chirurgie;
        }
        if ($chirurgie->getPreparationsMateriel()->isEmpty()) {
            throw new UnprocessableEntityHttpException('La chirurgie ne possède aucune préparation de matériel.');
        }
        foreach ($chirurgie->getPreparationsMateriel() as $preparation) {
            if (!$preparation->isCoche() && !$preparation->isAbsent()) {
                throw new UnprocessableEntityHttpException('Tout le matériel doit être déclaré prêt ou absent avant la validation.');
            }
            if ($preparation->isAbsent()) {
                throw new UnprocessableEntityHttpException('Une chirurgie ne peut pas être validée tant qu’un matériel est absent.');
            }
        }

        $now = new \DateTimeImmutable();
        $chirurgie->setValide(true)->setValideLe($now)->setValidePar($this->authenticatedUser->getUser());
        $this->auditTrail->markModified($chirurgie, $now);
        $this->entityManager->flush();

        return $chirurgie;
    }
}
