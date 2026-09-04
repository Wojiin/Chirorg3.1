<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ChirurgieModele;
use App\Entity\Specialite;
use PHPUnit\Framework\TestCase;

final class ChirurgieModeleTest extends TestCase
{
    public function testItStoresATrimmedLabel(): void
    {
        $chirurgieModele = (new ChirurgieModele())->setIntitule('  Pontage coronarien  ');

        self::assertSame('Pontage coronarien', $chirurgieModele->getIntitule());
    }

    public function testSpecialiteMaintainsBothSidesOfTheRelation(): void
    {
        $specialite = (new Specialite())->setIntitule('Cardiologie');
        $chirurgieModele = new ChirurgieModele();

        $specialite->addChirurgieModele($chirurgieModele);

        self::assertSame($specialite, $chirurgieModele->getSpecialite());
        self::assertTrue($specialite->getChirurgiesModeles()->contains($chirurgieModele));

        $specialite->removeChirurgieModele($chirurgieModele);

        self::assertNull($chirurgieModele->getSpecialite());
        self::assertFalse($specialite->getChirurgiesModeles()->contains($chirurgieModele));
    }
}
