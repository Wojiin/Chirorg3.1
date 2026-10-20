<?php

namespace App\Service;

use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOperatoireResume;
use App\Repository\ChirurgiePlanifieeRepository;

final readonly class ProgrammeOperatoireService
{
    // Injecte les services nécessaires au traitement du programme opératoire. Dépendances utilisées : ChirurgiePlanifieeRepository, ProgrammeOperatoireFactory.
    public function __construct(private ChirurgiePlanifieeRepository $repository, private ProgrammeOperatoireFactory $factory)
    {
    }

    // Retourne les programmes opératoires correspondant à la date, à la salle et au chirurgien demandés. Dépendances utilisées : ChirurgiePlanifieeRepository, ProgrammeOperatoireFactory.
    /** @return list<ProgrammeOperatoireResume> */
    public function list(?\DateTimeInterface $date = null, ?string $salle = null, ?int $chirurgienId = null, ?\DateTimeInterface $dateDebut = null, ?\DateTimeInterface $dateFin = null): array
    {
        return $this->factory->createSummaries($this->repository->findProgrammes($date, $salle, $chirurgienId, $dateDebut, $dateFin));
    }

    // Retourne le programme opératoire identifié par sa date, sa salle et son chirurgien. Dépendances utilisées : ChirurgiePlanifieeRepository, ProgrammeOperatoireFactory.
    public function one(\DateTimeInterface $date, string $salle, int $chirurgienId): ?ProgrammeOperatoire
    {
        return $this->factory->create($this->repository->findProgrammes($date, $salle, $chirurgienId));
    }
}
