<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\ArrayPaginator;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ParameterNotFound;
use ApiPlatform\State\ProviderInterface;
use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOperatoireResume;
use App\Error\ErrorMessage;
use App\Service\ProgrammeOperatoireService;
use App\Service\ProgrammeReferenceResolver;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ProgrammeOperatoire> */
final readonly class ProgrammeOperatoireProvider implements ProviderInterface
{
    public function __construct(private ProgrammeOperatoireService $service, private ProgrammeReferenceResolver $referenceResolver, private Pagination $pagination)
    {
    }

    /** @return ProgrammeOperatoire|ArrayPaginator<ProgrammeOperatoireResume> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ProgrammeOperatoire|ArrayPaginator
    {
        if (isset($uriVariables['date'], $uriVariables['salle'], $uriVariables['chirurgien'])) {
            $reference = $this->referenceResolver->resolve($uriVariables);

            return $this->service->one($reference->date, $reference->salle, $reference->chirurgienId)
                ?? throw new NotFoundHttpException(ErrorMessage::PROGRAMME_NOT_FOUND);
        }
        $date = $this->parameter($operation, 'date');
        $dateDebut = $this->parameter($operation, 'dateDebut');
        $dateFin = $this->parameter($operation, 'dateFin');
        $start = null === $dateDebut ? null : $this->referenceResolver->date((string) $dateDebut);
        $end = null === $dateFin ? null : $this->referenceResolver->date((string) $dateFin);
        if (null !== $start && null !== $end && $end < $start) {
            throw new BadRequestHttpException(ErrorMessage::DATE_RANGE_INVALID);
        }
        $salle = $this->parameter($operation, 'salle');
        $chirurgien = $this->parameter($operation, 'chirurgien');

        $programmes = $this->service->list(null === $date ? null : $this->referenceResolver->date((string) $date), null === $salle ? null : trim((string) $salle), null === $chirurgien ? null : (int) $chirurgien, $start, $end);
        [, $offset, $limit] = $this->pagination->getPagination($operation, $context);

        return new ArrayPaginator($programmes, $offset, $limit);
    }

    private function parameter(Operation $operation, string $name): mixed
    {
        $value = $operation->getParameters()?->get($name)?->getValue();

        return $value instanceof ParameterNotFound ? null : $value;
    }
}
