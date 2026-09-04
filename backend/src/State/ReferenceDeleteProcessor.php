<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\ReferenceDeletionGuard;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** @implements ProcessorInterface<object, mixed> */
final readonly class ReferenceDeleteProcessor implements ProcessorInterface
{
    /** @param ProcessorInterface<object, mixed> $removeProcessor */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private ReferenceDeletionGuard $deletionGuard,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->deletionGuard->assertCanDelete($data);

        return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}
