<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOrdreInput;
use App\Repository\ChirurgiePlanifieeRepository;
use App\Service\AuthenticatedUserProvider;
use App\Service\ProgrammeOperatoireService;
use App\Service\ProgrammeReferenceResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** @implements ProcessorInterface<ProgrammeOrdreInput, ProgrammeOperatoire> */
final readonly class ProgrammeOrdreProcessor implements ProcessorInterface
{
    public function __construct(private ChirurgiePlanifieeRepository $repository, private ProgrammeOperatoireService $service, private ProgrammeReferenceResolver $referenceResolver, private AuthenticatedUserProvider $authenticatedUser, private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProgrammeOperatoire
    {
        $reference = $this->referenceResolver->resolve($uriVariables);
        $items = $this->repository->findProgrammes($reference->date, $reference->salle, $reference->chirurgienId);
        if ([] === $items) {
            throw new NotFoundHttpException('Programme opératoire introuvable.');
        }
        $indexed = [];
        foreach ($items as $item) {
            $indexed[$item->getId()] = $item;
        }
        if (count($indexed) !== count($data->chirurgieIds) || array_diff(array_keys($indexed), $data->chirurgieIds)) {
            throw new UnprocessableEntityHttpException('La permutation doit contenir exactement toutes les chirurgies du programme.');
        }

        return $this->entityManager->wrapInTransaction(function () use ($data, $indexed, $reference): ProgrammeOperatoire {
            $now = new \DateTimeImmutable();
            $actor = $this->authenticatedUser->getUser()->getUserIdentifier();
            foreach ($data->chirurgieIds as $position => $id) {
                $indexed[$id]->setOrdre($position + 1)->setModifieLe($now)->setModifiePar($actor);
            }
            $this->entityManager->flush();

            return $this->service->one($reference->date, $reference->salle, $reference->chirurgienId) ?? throw new \LogicException('Le programme réordonné ne peut pas être relu.');
        });
    }
}
