<?php

namespace App\Tests\Unit\Service;

use App\Entity\PreparationMateriel;
use App\Service\PreparationProgressCalculator;
use PHPUnit\Framework\TestCase;

final class PreparationProgressCalculatorTest extends TestCase
{
    public function testItCalculatesCompletePreparationAndPartialValidation(): void
    {
        $calculator = new PreparationProgressCalculator();
        $progress = $calculator->calculate([(new PreparationMateriel())->setCoche(true), (new PreparationMateriel())->setAbsent(true)]);
        self::assertSame(['total' => 2, 'coches' => 1, 'absents' => 1, 'traites' => 2, 'complete' => true], $progress);
        self::assertSame(PreparationProgressCalculator::STATE_PARTIAL, $calculator->validationState(false, $progress));
        self::assertSame(PreparationProgressCalculator::STATE_VALIDATED, $calculator->validationState(true, $progress));
    }

    public function testAnEmptyChecklistIsNotComplete(): void
    {
        self::assertSame(['total' => 0, 'coches' => 0, 'absents' => 0, 'traites' => 0, 'complete' => false], (new PreparationProgressCalculator())->calculate([]));
    }
}
