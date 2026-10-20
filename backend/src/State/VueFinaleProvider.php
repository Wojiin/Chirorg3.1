<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\ChirurgieVueFinale;
use App\Error\ErrorMessage;
use App\Repository\ChirurgiePlanifieeRepository;
use App\Service\ChirurgieReadModelFactory;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ChirurgieVueFinale> */
final readonly class VueFinaleProvider implements ProviderInterface
{
    // Injecte les services nécessaires au traitement de la chirurgie planifiée. Dépendances utilisées : ChirurgiePlanifieeRepository, ChirurgieReadModelFactory.
    public function __construct(private ChirurgiePlanifieeRepository $repository, private ChirurgieReadModelFactory $factory)
    {
    }

    // Construit la représentation API de la chirurgie planifiée. Dépendances utilisées : ChirurgiePlanifieeRepository, ChirurgieReadModelFactory, ConflictHttpException, ErrorMessage, NotFoundHttpException, Operation.
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ChirurgieVueFinale
    {
        $chirurgie = $this->repository->findVueFinaleData((int) ($uriVariables['id'] ?? 0)) ?? throw new NotFoundHttpException(ErrorMessage::CHIRURGIE_PLANIFIEE_NOT_FOUND);
        if (!$chirurgie->isValide()) {
            throw new ConflictHttpException(ErrorMessage::FINAL_VIEW_REQUIRES_VALIDATION);
        }

        return $this->factory->createFinalView($chirurgie);
    }
}
