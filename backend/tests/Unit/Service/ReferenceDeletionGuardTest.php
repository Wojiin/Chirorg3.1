<?php

namespace App\Tests\Unit\Service;

use App\Entity\Chirurgien;
use App\Entity\Specialite;
use App\Service\ReferenceDeletionGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class ReferenceDeletionGuardTest extends TestCase
{
    public function testItAllowsAnUnusedReference(): void
    {
        $guard = new ReferenceDeletionGuard();

        $guard->assertCanDelete(new Specialite());

        self::addToAssertionCount(1);
    }

    public function testItRejectsAUsedReference(): void
    {
        $specialite = new Specialite();
        $specialite->addChirurgien(new Chirurgien());

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('Cette ressource ne peut pas être supprimée car elle est utilisée.');

        (new ReferenceDeletionGuard())->assertCanDelete($specialite);
    }
}
