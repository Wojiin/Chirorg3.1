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
    // Injecte les services nécessaires au traitement de la préparation du matériel. Dépendances utilisées : PreparationMaterielService.
    public function __construct(private PreparationMaterielService $service)
    {
    }

    // Applique l'opération d'écriture demandée pour la préparation du matériel. Dépendances utilisées : Operation, PreparationMaterielService.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PreparationMateriel
    {
        return $this->service->update((int) ($uriVariables['id'] ?? 0), $data);
    }
}
