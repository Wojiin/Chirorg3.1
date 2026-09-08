<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\Materiel;
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

    public function testItMaintainsEveryReferenceCollectionWithoutDuplicates(): void
    {
        $specialite = new Specialite();
        $chirurgien = new Chirurgien();
        $materiel = new Materiel();
        $chirurgieModele = new ChirurgieModele();

        $specialite
            ->addChirurgien($chirurgien)
            ->addChirurgien($chirurgien)
            ->addMateriel($materiel)
            ->addMateriel($materiel)
            ->addChirurgieModele($chirurgieModele)
            ->addChirurgieModele($chirurgieModele);

        self::assertCount(1, $specialite->getChirurgiens());
        self::assertCount(1, $specialite->getMateriels());
        self::assertCount(1, $specialite->getChirurgiesModeles());
        self::assertSame($specialite, $chirurgien->getSpecialite());
        self::assertSame($specialite, $materiel->getSpecialite());
        self::assertSame($specialite, $chirurgieModele->getSpecialite());

        $specialite
            ->removeChirurgien($chirurgien)
            ->removeMateriel($materiel)
            ->removeChirurgieModele($chirurgieModele);

        self::assertNull($chirurgien->getSpecialite());
        self::assertNull($materiel->getSpecialite());
        self::assertNull($chirurgieModele->getSpecialite());
    }
}
