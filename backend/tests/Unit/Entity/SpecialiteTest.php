<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Specialite;
use PHPUnit\Framework\TestCase;

final class SpecialiteTest extends TestCase
{
    public function testItStoresATrimmedLabel(): void
    {
        $specialite = (new Specialite())->setIntitule('  Orthopédie  ');

        self::assertSame('Orthopédie', $specialite->getIntitule());
        self::assertNull($specialite->getId());
    }
}
