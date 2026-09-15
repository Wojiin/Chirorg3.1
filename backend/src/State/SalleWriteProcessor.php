<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Salle;
use App\Error\ErrorMessage;
use App\Repository\ChirurgiePlanifieeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** @implements ProcessorInterface<object, mixed> */
final readonly class SalleWriteProcessor implements ProcessorInterface
{
    /** @param ProcessorInterface<object, mixed> $persistProcessor */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
        private ChirurgiePlanifieeRepository $chirurgies,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Salle) {
            throw new \InvalidArgumentException(ErrorMessage::SALLE_EXPECTED);
        }

        $original = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
        $ancienIntitule = $original['intitule'] ?? null;
        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        if (is_string($ancienIntitule) && $ancienIntitule !== $data->getIntitule()) {
            $this->chirurgies->renameSalle($ancienIntitule, (string) $data->getIntitule());
        }

        return $result;
    }
}
