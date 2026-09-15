<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Salle;
use PHPUnit\Framework\TestCase;

final class SalleTest extends TestCase
{
    public function testItStoresATrimmedLabel(): void
    {
        $salle = (new Salle())->setIntitule('  Salle D  ');

        self::assertSame('Salle D', $salle->getIntitule());
        self::assertNull($salle->getId());
    }
}
