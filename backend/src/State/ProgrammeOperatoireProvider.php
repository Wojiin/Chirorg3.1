<?php

namespace App\State;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ParameterNotFound;
use ApiPlatform\State\ProviderInterface;
use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOperatoireResume;
use App\Service\ProgrammeOperatoireService;
use App\Service\ProgrammeReferenceResolver;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ProgrammeOperatoire> */
final readonly class ProgrammeOperatoireProvider implements ProviderInterface
{
    public function __construct(private ProgrammeOperatoireService $service, private ProgrammeReferenceResolver $referenceResolver)
    {
    }

    /** @return ProgrammeOperatoire|list<ProgrammeOperatoireResume> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ProgrammeOperatoire|array
    {
        if (isset($uriVariables['date'], $uriVariables['salle'], $uriVariables['chirurgien'])) {
            $reference = $this->referenceResolver->resolve($uriVariables);
            $final = $operation instanceof HttpOperation && str_ends_with($operation->getUriTemplate() ?? '', '/vue-finale');

            return $this->service->one($reference->date, $reference->salle, $reference->chirurgienId, $final)
                ?? throw new NotFoundHttpException('Programme opératoire introuvable.');
        }
        $date = $this->parameter($operation, 'date');
        $dateDebut = $this->parameter($operation, 'dateDebut');
        $dateFin = $this->parameter($operation, 'dateFin');
        $start = null === $dateDebut ? null : $this->referenceResolver->date((string) $dateDebut);
        $end = null === $dateFin ? null : $this->referenceResolver->date((string) $dateFin);
        if (null !== $start && null !== $end && $end < $start) {
            throw new BadRequestHttpException('dateFin doit être postérieure ou égale à dateDebut.');
        }
        $salle = $this->parameter($operation, 'salle');
        $chirurgien = $this->parameter($operation, 'chirurgien');

        return $this->service->list(null === $date ? null : $this->referenceResolver->date((string) $date), null === $salle ? null : trim((string) $salle), null === $chirurgien ? null : (int) $chirurgien, $start, $end);
    }

    private function parameter(Operation $operation, string $name): mixed
    {
        $value = $operation->getParameters()?->get($name)?->getValue();

        return $value instanceof ParameterNotFound ? null : $value;
    }
}
