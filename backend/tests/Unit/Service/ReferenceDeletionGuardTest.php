<?php

namespace App\Tests\Unit\Service;

use App\Entity\Chirurgien;
use App\Entity\ListeMateriel;
use App\Service\ReferenceDeletionGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class ReferenceDeletionGuardTest extends TestCase
{
    public function testItAllowsAnUnusedReference(): void
    {
        $guard = new ReferenceDeletionGuard();

        $guard->assertCanDelete(new Chirurgien());

        self::addToAssertionCount(1);
    }

    public function testItRejectsAUsedReference(): void
    {
        $chirurgien = new Chirurgien();
        $chirurgien->addListeMateriel(new ListeMateriel());

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessageIs('Cette ressource ne peut pas être supprimée car elle est utilisée.');

        (new ReferenceDeletionGuard())->assertCanDelete($chirurgien);
    }
}
