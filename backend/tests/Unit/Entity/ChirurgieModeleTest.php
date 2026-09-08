<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ChirurgieModele;
use App\Entity\FicheTechnique;
use App\Entity\ListeMateriel;
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

    public function testItMaintainsItsDependentCollections(): void
    {
        $chirurgieModele = new ChirurgieModele();
        $fiche = new FicheTechnique();
        $liste = new ListeMateriel();

        $chirurgieModele
            ->addFicheTechnique($fiche)
            ->addFicheTechnique($fiche)
            ->addListeMateriel($liste)
            ->addListeMateriel($liste);

        self::assertCount(1, $chirurgieModele->getFichesTechniques());
        self::assertCount(1, $chirurgieModele->getListesMateriel());
        self::assertSame($chirurgieModele, $fiche->getChirurgieModele());
        self::assertSame($chirurgieModele, $liste->getChirurgieModele());

        $chirurgieModele->removeFicheTechnique($fiche)->removeListeMateriel($liste);

        self::assertNull($fiche->getChirurgieModele());
        self::assertNull($liste->getChirurgieModele());
    }
}
