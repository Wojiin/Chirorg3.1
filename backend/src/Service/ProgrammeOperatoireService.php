<?php

namespace App\Service;

use App\Dto\ProgrammeOperatoire;
use App\Repository\ChirurgiePlanifieeRepository;

final readonly class ProgrammeOperatoireService
{
    public function __construct(private ChirurgiePlanifieeRepository $repository, private ProgrammeOperatoireFactory $factory)
    {
    }

    /** @return list<ProgrammeOperatoire> */
    public function list(?\DateTimeInterface $date = null, ?string $salle = null, ?int $chirurgienId = null): array
    {
        $groups = [];
        foreach ($this->repository->findProgrammes($date, $salle, $chirurgienId) as $item) {
            $key = $item->getDateProgrammee()?->format('Y-m-d').'|'.$item->getSalle().'|'.$item->getChirurgien()?->getId();
            $groups[$key][] = $item;
        }

        return array_values(array_filter(array_map($this->factory->create(...), $groups)));
    }

    public function one(\DateTimeInterface $date, string $salle, int $chirurgienId): ?ProgrammeOperatoire
    {
        return $this->factory->create($this->repository->findProgrammes($date, $salle, $chirurgienId));
    }
}
