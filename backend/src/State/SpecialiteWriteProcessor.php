<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Specialite;
use App\Error\ErrorMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Empêche le renommage de la spécialité technique de repli.
 *
 * @implements ProcessorInterface<object, mixed>
 */
final readonly class SpecialiteWriteProcessor implements ProcessorInterface
{
    /** @param ProcessorInterface<object, mixed> $persistProcessor */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Specialite) {
            throw new \InvalidArgumentException(ErrorMessage::SPECIALITE_EXPECTED);
        }

        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
        if (
            Specialite::SANS_SPECIALITE === ($originalData['intitule'] ?? null)
            && Specialite::SANS_SPECIALITE !== $data->getIntitule()
        ) {
            throw new ConflictHttpException(ErrorMessage::DEFAULT_SPECIALITE_IMMUTABLE);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
