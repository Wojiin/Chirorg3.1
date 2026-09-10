<?php

namespace App\Service;

use App\Dto\PreparationMaterielInput;
use App\Entity\PreparationMateriel;
use App\Error\ErrorMessage;
use App\Repository\PreparationMaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class PreparationMaterielService
{
    public function __construct(private PreparationMaterielRepository $repository, private AuthenticatedUserProvider $authenticatedUser, private ChirurgieAuditTrail $auditTrail, private EntityManagerInterface $entityManager)
    {
    }

    public function update(int $id, PreparationMaterielInput $input): PreparationMateriel
    {
        $preparation = $this->repository->find($id) ?? throw new NotFoundHttpException(ErrorMessage::PREPARATION_NOT_FOUND);
        $chirurgie = $preparation->getChirurgiePlanifiee();
        if ($chirurgie?->isValide()) {
            throw new ConflictHttpException(ErrorMessage::PREPARATION_LOCKED);
        }
        $coche = $input->coche ?? $preparation->isCoche();
        $absent = $input->absent ?? $preparation->isAbsent();
        if ($coche && $absent) {
            throw new UnprocessableEntityHttpException(ErrorMessage::MATERIEL_STATE_CONFLICT);
        }

        $now = new \DateTimeImmutable();
        $preparation->setCoche($coche)->setAbsent($absent)->setCocheLe($coche ? $now : null)->setCochePar($coche ? $this->authenticatedUser->getUser() : null);
        if (null !== $chirurgie) {
            $this->auditTrail->markModified($chirurgie, $now);
        }
        $this->entityManager->flush();

        return $preparation;
    }
}
