<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Chirurgien;
use App\Entity\ListeMateriel;
use App\Entity\Specialite;
use PHPUnit\Framework\TestCase;

final class ChirurgienTest extends TestCase
{
    public function testItStoresATrimmedIdentity(): void
    {
        $chirurgien = (new Chirurgien())
            ->setPrenom('  Claire ')
            ->setNom(' Martin  ');

        self::assertSame('Claire', $chirurgien->getPrenom());
        self::assertSame('Martin', $chirurgien->getNom());
    }

    public function testSpecialiteMaintainsBothSidesOfTheRelation(): void
    {
        $specialite = (new Specialite())->setIntitule('Cardiologie');
        $chirurgien = new Chirurgien();

        $specialite->addChirurgien($chirurgien);

        self::assertSame($specialite, $chirurgien->getSpecialite());
        self::assertTrue($specialite->getChirurgiens()->contains($chirurgien));

        $specialite->removeChirurgien($chirurgien);

        self::assertNull($chirurgien->getSpecialite());
        self::assertFalse($specialite->getChirurgiens()->contains($chirurgien));
    }

    public function testItMaintainsListeMaterielRelationWithoutDuplicates(): void
    {
        $chirurgien = new Chirurgien();
        $liste = new ListeMateriel();

        $chirurgien->addListeMateriel($liste)->addListeMateriel($liste);

        self::assertCount(1, $chirurgien->getListesMateriel());
        self::assertSame($chirurgien, $liste->getChirurgien());

        $chirurgien->removeListeMateriel($liste);

        self::assertNull($liste->getChirurgien());
        self::assertFalse($chirurgien->getListesMateriel()->contains($liste));
    }
}
