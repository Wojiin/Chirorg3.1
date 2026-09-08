<?php

namespace App\Service;

use App\Dto\ProgrammePlanificationInput;
use App\Dto\ProgrammePlanificationOutput;
use App\Entity\ChirurgiePlanifiee;
use App\Repository\ChirurgieModeleRepository;
use App\Repository\ChirurgienRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class ProgrammePlanificationService
{
    public function __construct(private ChirurgienRepository $chirurgiens, private ChirurgieModeleRepository $modeles, private ProgrammeOrderAllocator $orderAllocator, private PreparationMaterielInitializer $initializer, private ProgrammeOperatoireService $programmes, private ChirurgieAuditTrail $auditTrail, private EntityManagerInterface $entityManager)
    {
    }

    public function plan(ProgrammePlanificationInput $input): ProgrammePlanificationOutput
    {
        if (null === $input->dateProgrammee || null === $input->salle || null === $input->chirurgienId) {
            throw new BadRequestHttpException('Un programme opératoire valide est attendu.');
        }
        $chirurgien = $this->chirurgiens->find($input->chirurgienId) ?? throw new NotFoundHttpException('Chirurgien introuvable.');
        $salle = trim($input->salle);
        $models = [];
        foreach ($input->chirurgieModeleIds as $modelId) {
            $models[] = $this->modeles->find($modelId) ?? throw new NotFoundHttpException(sprintf('Chirurgie modèle %d introuvable.', $modelId));
        }

        return $this->entityManager->wrapInTransaction(function () use ($input, $chirurgien, $models, $salle): ProgrammePlanificationOutput {
            $order = $this->orderAllocator->reserveNextOrder($input->dateProgrammee, $salle, $chirurgien);
            foreach ($models as $modele) {
                $item = (new ChirurgiePlanifiee())->setDateProgrammee($input->dateProgrammee)->setSalle($salle)->setOrdre($order++)->setChirurgien($chirurgien)->setChirurgieModele($modele);
                $this->auditTrail->markCreated($item);
                $this->entityManager->persist($item);
                $this->initializer->initialize($item);
            }
            $this->entityManager->flush();
            $programme = $this->programmes->one($input->dateProgrammee, $salle, $input->chirurgienId) ?? throw new \LogicException('Le programme créé ne peut pas être relu.');

            return ProgrammePlanificationOutput::fromProgramme($programme);
        });
    }
}
