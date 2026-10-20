<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ProgrammePlanificationInput;
use App\Dto\ProgrammePlanificationOutput;
use App\Service\ProgrammePlanificationService;

/** @implements ProcessorInterface<ProgrammePlanificationInput, ProgrammePlanificationOutput> */
final readonly class ProgrammePlanificationProcessor implements ProcessorInterface
{
    // Injecte les services nécessaires au traitement du programme opératoire. Dépendances utilisées : ProgrammePlanificationService.
    public function __construct(private ProgrammePlanificationService $service)
    {
    }

    // Applique l'opération d'écriture demandée pour le programme opératoire. Dépendances utilisées : Operation, ProgrammePlanificationService.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProgrammePlanificationOutput
    {
        return $this->service->plan($data);
    }
}
