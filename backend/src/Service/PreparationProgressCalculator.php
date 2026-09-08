<?php

namespace App\Service;

use App\Entity\PreparationMateriel;

final class PreparationProgressCalculator
{
    public const string STATE_VALIDATED = 'VALIDEE';
    public const string STATE_PARTIAL = 'VALIDATION_PARTIELLE';
    public const string STATE_PREPARING = 'EN_PREPARATION';

    /**
     * @param iterable<PreparationMateriel> $preparations
     *
     * @return array{total: int, coches: int, absents: int, traites: int, complete: bool}
     */
    public function calculate(iterable $preparations): array
    {
        $total = $coches = $absents = 0;
        foreach ($preparations as $preparation) {
            ++$total;
            $coches += $preparation->isCoche() ? 1 : 0;
            $absents += $preparation->isAbsent() ? 1 : 0;
        }
        $traites = $coches + $absents;

        return ['total' => $total, 'coches' => $coches, 'absents' => $absents, 'traites' => $traites, 'complete' => $total > 0 && $total === $traites];
    }

    /** @param array{total: int, coches: int, absents: int, traites: int, complete: bool} $progress */
    public function validationState(bool $validated, array $progress): string
    {
        if ($validated) {
            return self::STATE_VALIDATED;
        }

        return $progress['complete'] && $progress['absents'] > 0 ? self::STATE_PARTIAL : self::STATE_PREPARING;
    }
}
