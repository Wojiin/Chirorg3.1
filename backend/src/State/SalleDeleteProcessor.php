<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Salle;
use App\Error\ErrorMessage;
use App\Repository\ChirurgiePlanifieeRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/** @implements ProcessorInterface<object, mixed> */
final readonly class SalleDeleteProcessor implements ProcessorInterface
{
    // Injecte les services nécessaires au traitement de la salle opératoire. Dépendances utilisées : ChirurgiePlanifieeRepository, ProcessorInterface.
    /** @param ProcessorInterface<object, mixed> $removeProcessor */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private ChirurgiePlanifieeRepository $chirurgies,
    ) {
    }

    // Applique l'opération d'écriture demandée pour la salle opératoire. Dépendances utilisées : ChirurgiePlanifieeRepository, ConflictHttpException, ErrorMessage, Operation, ProcessorInterface.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Salle) {
            throw new \InvalidArgumentException(ErrorMessage::SALLE_EXPECTED);
        }
        if ($this->chirurgies->count(['salle' => $data->getIntitule()]) > 0) {
            throw new ConflictHttpException(ErrorMessage::SALLE_IN_USE);
        }

        return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}
