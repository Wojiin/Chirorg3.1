<?php

namespace App\Service;

use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOperatoireResume;
use App\Repository\ChirurgiePlanifieeRepository;

final readonly class ProgrammeOperatoireService
{
    public function __construct(private ChirurgiePlanifieeRepository $repository, private ProgrammeOperatoireFactory $factory)
    {
    }

    /** @return list<ProgrammeOperatoireResume> */
    public function list(?\DateTimeInterface $date = null, ?string $salle = null, ?int $chirurgienId = null, ?\DateTimeInterface $dateDebut = null, ?\DateTimeInterface $dateFin = null): array
    {
        return $this->factory->createSummaries($this->repository->findProgrammes($date, $salle, $chirurgienId, $dateDebut, $dateFin));
    }

    public function one(\DateTimeInterface $date, string $salle, int $chirurgienId): ?ProgrammeOperatoire
    {
        return $this->factory->create($this->repository->findProgrammes($date, $salle, $chirurgienId));
    }
}
