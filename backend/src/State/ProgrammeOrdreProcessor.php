<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOrdreInput;
use App\Service\ProgrammeOrderingService;

/** @implements ProcessorInterface<ProgrammeOrdreInput, ProgrammeOperatoire> */
final readonly class ProgrammeOrdreProcessor implements ProcessorInterface
{
    // Injecte les services nécessaires au traitement du programme opératoire. Dépendances utilisées : ProgrammeOrderingService.
    public function __construct(private ProgrammeOrderingService $service)
    {
    }

    // Applique l'opération d'écriture demandée pour le programme opératoire. Dépendances utilisées : Operation, ProgrammeOrderingService.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProgrammeOperatoire
    {
        return $this->service->reorder($data, $uriVariables);
    }
}
