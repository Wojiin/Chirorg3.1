<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ListeMateriel;
use App\Entity\Materiel;
use PHPUnit\Framework\TestCase;

final class ListeMaterielTest extends TestCase
{
    public function testItDoesNotDuplicateMaterielAndMaintainsBothSides(): void
    {
        $liste = (new ListeMateriel())->setIntitule('  Standard pontage  ');
        $materiel = new Materiel();

        $liste->addMateriel($materiel)->addMateriel($materiel);

        self::assertSame('Standard pontage', $liste->getIntitule());
        self::assertCount(1, $liste->getMateriels());
        self::assertTrue($materiel->getListesMateriel()->contains($liste));

        $liste->removeMateriel($materiel);

        self::assertFalse($liste->getMateriels()->contains($materiel));
        self::assertFalse($materiel->getListesMateriel()->contains($liste));
    }

    public function testOwnersMaintainBothSidesOfTheirRelations(): void
    {
        $liste = new ListeMateriel();
        $chirurgien = new Chirurgien();
        $chirurgieModele = new ChirurgieModele();

        $chirurgien->addListeMateriel($liste);
        $chirurgieModele->addListeMateriel($liste);

        self::assertSame($chirurgien, $liste->getChirurgien());
        self::assertSame($chirurgieModele, $liste->getChirurgieModele());
        self::assertTrue($chirurgien->getListesMateriel()->contains($liste));
        self::assertTrue($chirurgieModele->getListesMateriel()->contains($liste));
    }
}
