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
    public function __construct(private ProgrammePlanificationService $service)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProgrammePlanificationOutput
    {
        return $this->service->plan($data);
    }
}
