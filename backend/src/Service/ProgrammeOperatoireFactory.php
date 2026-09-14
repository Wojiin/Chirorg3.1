<?php

namespace App\Service;

use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOperatoireResume;
use App\Entity\ChirurgiePlanifiee;

final readonly class ProgrammeOperatoireFactory
{
    public function __construct(private ChirurgieReadModelFactory $chirurgieFactory, private PreparationProgressCalculator $progressCalculator)
    {
    }

    /** @param list<ChirurgiePlanifiee> $chirurgies */
    public function create(array $chirurgies): ?ProgrammeOperatoire
    {
        $first = $chirurgies[0] ?? null;
        $date = $first?->getDateProgrammee();
        $chirurgien = $first?->getChirurgien();
        $salle = $first?->getSalle();
        if (null === $first || null === $date || null === $chirurgien || null === $chirurgien->getId() || null === $salle) {
            return null;
        }
        usort($chirurgies, static fn (ChirurgiePlanifiee $a, ChirurgiePlanifiee $b): int => [$a->getOrdre() ?? PHP_INT_MAX, $a->getId() ?? PHP_INT_MAX] <=> [$b->getOrdre() ?? PHP_INT_MAX, $b->getId() ?? PHP_INT_MAX]);
        $items = array_map(fn (ChirurgiePlanifiee $item): array => $this->chirurgieFactory->createProgrammeItem($item), $chirurgies);

        return new ProgrammeOperatoire(
            rawurlencode($date->format('Y-m-d').'|'.$salle.'|'.$chirurgien->getId()), $date->format('Y-m-d'), $salle,
            ['id' => $chirurgien->getId(), 'prenom' => (string) $chirurgien->getPrenom(), 'nom' => (string) $chirurgien->getNom()],
            count($items), count(array_filter($chirurgies, static fn (ChirurgiePlanifiee $item): bool => $item->isValide())), $items,
        );
    }

    /**
     * @param list<ChirurgiePlanifiee> $chirurgies
     *
     * @return list<ProgrammeOperatoireResume>
     */
    public function createSummaries(array $chirurgies): array
    {
        $groups = [];
        foreach ($chirurgies as $chirurgie) {
            $key = $chirurgie->getDateProgrammee()?->format('Y-m-d').'|'.$chirurgie->getSalle().'|'.$chirurgie->getChirurgien()?->getId();
            $groups[$key][] = $chirurgie;
        }
        $result = [];
        foreach ($groups as $items) {
            $programme = $this->create($items);
            if (null === $programme) {
                continue;
            }
            $progress = ['total' => 0, 'coches' => 0, 'absents' => 0, 'traites' => 0, 'complete' => false];
            foreach ($items as $item) {
                $part = $this->progressCalculator->calculate($item->getPreparationsMateriel());
                foreach (['total', 'coches', 'absents', 'traites'] as $field) {
                    $progress[$field] += $part[$field];
                }
            }
            $progress['complete'] = $progress['total'] > 0 && $progress['total'] === $progress['traites'];
            $result[] = new ProgrammeOperatoireResume($programme->id, $programme->date, $programme->salle, $programme->chirurgien, $programme->nombreChirurgies, $programme->nombreChirurgiesValidees, $progress, $items[0]->getCreePar());
        }

        return $result;
    }
}
