<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ChirurgieModele;
use App\Entity\FicheTechnique;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class FicheTechniqueTest extends TestCase
{
    public function testItNormalizesOptionalContent(): void
    {
        $fiche = (new FicheTechnique())
            ->setTitre('  Installation  ')
            ->setDescription('   ')
            ->setLienImage(null);

        self::assertSame('Installation', $fiche->getTitre());
        self::assertNull($fiche->getDescription());
        self::assertNull($fiche->getLienImage());
    }

    public function testItRequiresTextOrAnImage(): void
    {
        $fiche = (new FicheTechnique())
            ->setTitre('Installation')
            ->setOrdre(0)
            ->setChirurgieModele(new ChirurgieModele());
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        self::assertCount(1, $validator->validate($fiche));
    }

    public function testChirurgieModeleMaintainsBothSidesOfTheRelation(): void
    {
        $chirurgieModele = new ChirurgieModele();
        $fiche = new FicheTechnique();

        $chirurgieModele->addFicheTechnique($fiche);

        self::assertSame($chirurgieModele, $fiche->getChirurgieModele());
        self::assertTrue($chirurgieModele->getFichesTechniques()->contains($fiche));

        $chirurgieModele->removeFicheTechnique($fiche);

        self::assertNull($fiche->getChirurgieModele());
        self::assertFalse($chirurgieModele->getFichesTechniques()->contains($fiche));
    }
}
