<?php

namespace App\Service;

use App\Dto\ProgrammePlanificationInput;
use App\Dto\ProgrammePlanificationOutput;
use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use App\Error\ErrorMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class ProgrammePlanificationService
{
    public function __construct(private ProgrammeOrderAllocator $orderAllocator, private PreparationMaterielInitializer $initializer, private ProgrammeOperatoireService $programmes, private ChirurgieAuditTrail $auditTrail, private EntityManagerInterface $entityManager)
    {
    }

    public function plan(ProgrammePlanificationInput $input): ProgrammePlanificationOutput
    {
        if (null === $input->dateProgrammee || null === $input->salle || null === $input->chirurgienId) {
            throw new BadRequestHttpException(ErrorMessage::PROGRAMME_INVALID);
        }
        $chirurgien = $this->entityManager->find(Chirurgien::class, $input->chirurgienId) ?? throw new NotFoundHttpException(ErrorMessage::CHIRURGIEN_NOT_FOUND);
        $salle = trim($input->salle);
        $models = [];
        foreach ($input->chirurgieModeleIds as $modelId) {
            $modele = $this->entityManager->find(ChirurgieModele::class, $modelId) ?? throw new NotFoundHttpException(ErrorMessage::chirurgieModeleNotFound($modelId));
            if ($modele->getSpecialite() !== $chirurgien->getSpecialite()) {
                throw new UnprocessableEntityHttpException(ErrorMessage::CHIRURGIE_MODELE_SPECIALITE_MISMATCH);
            }
            $models[] = $modele;
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
            $programme = $this->programmes->one($input->dateProgrammee, $salle, $input->chirurgienId) ?? throw new \LogicException(ErrorMessage::PROGRAMME_CREATED_UNREADABLE);

            return ProgrammePlanificationOutput::fromProgramme($programme);
        });
    }
}
