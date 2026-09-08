<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PreparationMaterielInput;
use App\Entity\PreparationMateriel;
use App\Repository\PreparationMaterielRepository;
use App\Service\AuthenticatedUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** @implements ProcessorInterface<PreparationMaterielInput, PreparationMateriel> */
final readonly class PreparationMaterielCocherProcessor implements ProcessorInterface
{
    public function __construct(private PreparationMaterielRepository $repository, private AuthenticatedUserProvider $authenticatedUser, private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PreparationMateriel
    {
        $preparation = $this->repository->find((int) ($uriVariables['id'] ?? 0)) ?? throw new NotFoundHttpException('Préparation de matériel introuvable.');
        if ($preparation->getChirurgiePlanifiee()?->isValide()) {
            throw new ConflictHttpException('La préparation d’une chirurgie validée est verrouillée.');
        }
        $coche = $data->coche ?? false;
        $absent = $data->absent ?? false;
        if ($coche && $absent) {
            throw new UnprocessableEntityHttpException('Un matériel ne peut pas être à la fois prêt et absent.');
        }
        $now = new \DateTimeImmutable();
        $preparation->setCoche($coche)->setAbsent($absent)->setCocheLe($coche ? $now : null)->setCochePar($coche ? $this->authenticatedUser->getUser() : null);
        $preparation->getChirurgiePlanifiee()?->setModifieLe($now)->setModifiePar($this->authenticatedUser->getUser()->getUserIdentifier());
        $this->entityManager->flush();

        return $preparation;
    }
}
