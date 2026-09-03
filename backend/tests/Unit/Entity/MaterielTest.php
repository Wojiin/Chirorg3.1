<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Materiel;
use App\Entity\Specialite;
use PHPUnit\Framework\TestCase;

final class MaterielTest extends TestCase
{
    public function testItNormalizesItsTextValues(): void
    {
        $materiel = (new Materiel())
            ->setIntitule('  Urétéroscope  ')
            ->setAdresse('   ')
            ->setTypeMateriel(null);

        self::assertSame('Urétéroscope', $materiel->getIntitule());
        self::assertNull($materiel->getAdresse());
        self::assertNull($materiel->getTypeMateriel());
    }

    public function testSpecialiteMaintainsBothSidesOfTheRelation(): void
    {
        $specialite = (new Specialite())->setIntitule('Urologie');
        $materiel = new Materiel();

        $specialite->addMateriel($materiel);

        self::assertSame($specialite, $materiel->getSpecialite());
        self::assertTrue($specialite->getMateriels()->contains($materiel));

        $specialite->removeMateriel($materiel);

        self::assertNull($materiel->getSpecialite());
        self::assertFalse($specialite->getMateriels()->contains($materiel));
    }
}
