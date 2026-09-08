<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ProgrammePlanificationInput;
use App\Dto\ProgrammePlanificationOutput;
use App\Entity\ChirurgiePlanifiee;
use App\Repository\ChirurgieModeleRepository;
use App\Repository\ChirurgienRepository;
use App\Repository\ChirurgiePlanifieeRepository;
use App\Service\AuthenticatedUserProvider;
use App\Service\PreparationMaterielInitializer;
use App\Service\ProgrammeOperatoireService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<ProgrammePlanificationInput, ProgrammePlanificationOutput> */
final readonly class ProgrammePlanificationProcessor implements ProcessorInterface
{
    public function __construct(private ChirurgienRepository $chirurgiens, private ChirurgieModeleRepository $modeles, private ChirurgiePlanifieeRepository $chirurgies, private PreparationMaterielInitializer $initializer, private ProgrammeOperatoireService $programmes, private AuthenticatedUserProvider $authenticatedUser, private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProgrammePlanificationOutput
    {
        if (null === $data->dateProgrammee || null === $data->salle || null === $data->chirurgienId) {
            throw new BadRequestHttpException('Un programme opératoire valide est attendu.');
        }
        $chirurgien = $this->chirurgiens->find($data->chirurgienId) ?? throw new NotFoundHttpException('Chirurgien introuvable.');
        $actor = $this->authenticatedUser->getUser()->getUserIdentifier();
        $salle = trim($data->salle);
        $order = $this->chirurgies->nextOrder($data->dateProgrammee, $salle, $chirurgien);
        foreach ($data->chirurgieModeleIds as $modelId) {
            $modele = $this->modeles->find($modelId) ?? throw new NotFoundHttpException(sprintf('Chirurgie modèle %d introuvable.', $modelId));
            $item = (new ChirurgiePlanifiee())->setDateProgrammee($data->dateProgrammee)->setSalle($salle)->setOrdre($order++)->setChirurgien($chirurgien)->setChirurgieModele($modele)->setCreePar($actor)->setModifiePar($actor);
            $this->entityManager->persist($item);
            $this->initializer->initialize($item);
        }
        $this->entityManager->flush();

        $programme = $this->programmes->one($data->dateProgrammee, $salle, $data->chirurgienId) ?? throw new \LogicException('Le programme créé ne peut pas être relu.');

        return ProgrammePlanificationOutput::fromProgramme($programme);
    }
}
