<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ChirurgiePlanifiee;
use App\Repository\ChirurgiePlanifieeRepository;
use App\Service\AuthenticatedUserProvider;
use App\Service\PreparationMaterielInitializer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/** @implements ProcessorInterface<ChirurgiePlanifiee, ChirurgiePlanifiee> */
final readonly class ChirurgiePlanifieeWriteProcessor implements ProcessorInterface
{
    /** @param ProcessorInterface<ChirurgiePlanifiee, ChirurgiePlanifiee> $persist */
    public function __construct(#[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')] private ProcessorInterface $persist, private PreparationMaterielInitializer $initializer, private ChirurgiePlanifieeRepository $repository, private AuthenticatedUserProvider $authenticatedUser)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ChirurgiePlanifiee
    {
        $actor = $this->authenticatedUser->getUser()->getUserIdentifier();
        $new = null === $data->getId();
        $data->setModifieLe(new \DateTimeImmutable())->setModifiePar($actor);
        if ($new) {
            $date = $data->getDateProgrammee();
            $salle = $data->getSalle();
            $chirurgien = $data->getChirurgien();
            if (null === $date || null === $salle || null === $chirurgien) {
                throw new BadRequestHttpException('Date, salle et chirurgien sont requis.');
            }
            $data->setOrdre($this->repository->nextOrder($date, $salle, $chirurgien))->setCreePar($actor);
            $this->initializer->initialize($data);
        }
        $result = $this->persist->process($data, $operation, $uriVariables, $context);

        return $result;
    }
}
