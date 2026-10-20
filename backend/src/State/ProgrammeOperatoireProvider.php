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
    // Injecte les services nécessaires au traitement du programme opératoire. Dépendances utilisées : Pagination, ProgrammeOperatoireService, ProgrammeReferenceResolver.
    public function __construct(private ProgrammeOperatoireService $service, private ProgrammeReferenceResolver $referenceResolver, private Pagination $pagination)
    {
    }

    // Construit la représentation API du programme opératoire. Dépendances utilisées : ArrayPaginator, BadRequestHttpException, ErrorMessage, NotFoundHttpException, Operation, Pagination, ProgrammeOperatoireService, ProgrammeReferenceResolver.
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
        $chirurgien = $this->positiveIntegerParameter($operation, 'chirurgien');

        $programmes = $this->service->list(null === $date ? null : $this->referenceResolver->date((string) $date), null === $salle ? null : trim((string) $salle), $chirurgien, $start, $end);
        [, $offset, $limit] = $this->pagination->getPagination($operation, $context);

        return new ArrayPaginator($programmes, $offset, $limit);
    }

    // Lit un paramètre déclaré sur l'opération API Platform. Dépendances utilisées : Operation.
    private function parameter(Operation $operation, string $name): mixed
    {
        $value = $operation->getParameters()?->get($name)?->getValue();

        return $value instanceof ParameterNotFound ? null : $value;
    }

    // Valide qu'un paramètre API est un entier strictement positif. Dépendances utilisées : BadRequestHttpException, ErrorMessage, Operation.
    private function positiveIntegerParameter(Operation $operation, string $name): ?int
    {
        $value = $this->parameter($operation, $name);
        if (null === $value) {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $integer) {
            throw new BadRequestHttpException(ErrorMessage::POSITIVE_NUMBER_REQUIRED);
        }

        return $integer;
    }
}
