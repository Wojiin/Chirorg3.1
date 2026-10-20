<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Specialite;
use App\Error\ErrorMessage;
use App\Service\SpecialiteDeletionService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** @implements ProcessorInterface<object, mixed> */
final readonly class SpecialiteDeleteProcessor implements ProcessorInterface
{
    // Injecte les services nécessaires au traitement de la spécialité. Dépendances utilisées : ProcessorInterface, SpecialiteDeletionService.
    /** @param ProcessorInterface<object, mixed> $removeProcessor */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private SpecialiteDeletionService $deletionService,
    ) {
    }

    // Applique l'opération d'écriture demandée pour la spécialité. Dépendances utilisées : ErrorMessage, Operation, ProcessorInterface, SpecialiteDeletionService.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Specialite) {
            throw new \InvalidArgumentException(ErrorMessage::SPECIALITE_EXPECTED);
        }

        $this->deletionService->reassignReferences($data);

        return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}
