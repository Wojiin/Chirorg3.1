<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PreparationMaterielInput;
use App\Entity\PreparationMateriel;
use App\Service\PreparationMaterielService;

/** @implements ProcessorInterface<PreparationMaterielInput, PreparationMateriel> */
final readonly class PreparationMaterielCocherProcessor implements ProcessorInterface
{
    public function __construct(private PreparationMaterielService $service)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PreparationMateriel
    {
        return $this->service->update((int) ($uriVariables['id'] ?? 0), $data);
    }
}
