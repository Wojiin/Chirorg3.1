<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\ProgrammeOperatoire;
use App\Service\ProgrammeOperatoireService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ProgrammeOperatoire> */
final readonly class ProgrammeOperatoireProvider implements ProviderInterface
{
    public function __construct(private ProgrammeOperatoireService $service)
    {
    }

    /** @return ProgrammeOperatoire|list<ProgrammeOperatoire> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ProgrammeOperatoire|array
    {
        if (isset($uriVariables['date'], $uriVariables['salle'], $uriVariables['chirurgien'])) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $uriVariables['date']);
            if (false === $date) {
                throw new BadRequestHttpException('La date doit respecter le format YYYY-MM-DD.');
            }

            return $this->service->one($date, (string) $uriVariables['salle'], (int) $uriVariables['chirurgien'])
                ?? throw new NotFoundHttpException('Programme opératoire introuvable.');
        }
        $filters = $context['filters'] ?? [];
        $date = isset($filters['date']) ? \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $filters['date']) : null;
        if (false === $date) {
            throw new BadRequestHttpException('La date doit respecter le format YYYY-MM-DD.');
        }

        return $this->service->list($date, isset($filters['salle']) ? (string) $filters['salle'] : null, isset($filters['chirurgien']) ? (int) $filters['chirurgien'] : null);
    }
}
