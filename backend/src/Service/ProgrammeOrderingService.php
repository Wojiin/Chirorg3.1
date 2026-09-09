<?php

namespace App\Service;

use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOrdreInput;
use App\Repository\ChirurgiePlanifieeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class ProgrammeOrderingService
{
    public function __construct(private ChirurgiePlanifieeRepository $repository, private ProgrammeOperatoireService $programmes, private ProgrammeReferenceResolver $referenceResolver, private ChirurgieAuditTrail $auditTrail, private EntityManagerInterface $entityManager)
    {
    }

    /** @param array<string, mixed> $uriVariables */
    public function reorder(ProgrammeOrdreInput $input, array $uriVariables): ProgrammeOperatoire
    {
        $reference = $this->referenceResolver->resolve($uriVariables);
        $items = $this->repository->findProgrammes($reference->date, $reference->salle, $reference->chirurgienId);
        if ([] === $items) {
            throw new NotFoundHttpException('Programme opératoire introuvable.');
        }
        $indexed = [];
        foreach ($items as $item) {
            if ($item->isValide()) {
                throw new ConflictHttpException('Un programme contenant une chirurgie validée ne peut plus être réordonné.');
            }
            $indexed[$item->getId()] = $item;
        }
        if (count($indexed) !== count($input->chirurgieIds) || array_diff(array_keys($indexed), $input->chirurgieIds)) {
            throw new UnprocessableEntityHttpException('La permutation doit contenir exactement toutes les chirurgies du programme.');
        }

        return $this->entityManager->wrapInTransaction(function () use ($input, $indexed, $reference): ProgrammeOperatoire {
            // Free the positive positions first so swaps cannot violate the SQL unique key.
            foreach ($input->chirurgieIds as $position => $id) {
                $indexed[$id]->setOrdre(-($position + 1));
            }
            $this->entityManager->flush();

            $now = new \DateTimeImmutable();
            foreach ($input->chirurgieIds as $position => $id) {
                $indexed[$id]->setOrdre($position + 1);
                $this->auditTrail->markModified($indexed[$id], $now);
            }
            $this->entityManager->flush();

            return $this->programmes->one($reference->date, $reference->salle, $reference->chirurgienId) ?? throw new \LogicException('Le programme réordonné ne peut pas être relu.');
        });
    }
}
