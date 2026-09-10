<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\ChirurgiePreparation;
use App\Error\ErrorMessage;
use App\Repository\ChirurgiePlanifieeRepository;
use App\Service\ChirurgieReadModelFactory;
use App\Service\PreparationMaterielInitializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ChirurgiePreparation> */
final readonly class ChirurgiePreparationProvider implements ProviderInterface
{
    public function __construct(private ChirurgiePlanifieeRepository $repository, private PreparationMaterielInitializer $initializer, private ChirurgieReadModelFactory $factory, private EntityManagerInterface $entityManager)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ChirurgiePreparation
    {
        $id = (int) ($uriVariables['id'] ?? 0);
        $chirurgie = $this->repository->findPreparationData($id) ?? throw new NotFoundHttpException(ErrorMessage::CHIRURGIE_PLANIFIEE_NOT_FOUND);
        if ($chirurgie->getPreparationsMateriel()->isEmpty()) {
            $this->initializer->initialize($chirurgie);
            $this->entityManager->flush();
        }

        return $this->factory->createPreparation($chirurgie);
    }
}
