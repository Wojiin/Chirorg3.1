<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ChirurgiePlanifiee;
use App\Service\ChirurgieValidationService;

/** @implements ProcessorInterface<ChirurgiePlanifiee, ChirurgiePlanifiee> */
final readonly class ChirurgieValidationProcessor implements ProcessorInterface
{
    // Injecte les services nécessaires au traitement de la chirurgie planifiée. Dépendances utilisées : ChirurgieValidationService.
    public function __construct(private ChirurgieValidationService $service)
    {
    }

    // Applique l'opération d'écriture demandée pour la chirurgie planifiée. Dépendances utilisées : ChirurgieValidationService, Operation.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ChirurgiePlanifiee
    {
        return $this->service->validate($data);
    }
}
