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

    public function testItRejectsNegativeOrderAndExternalImagePath(): void
    {
        $fiche = (new FicheTechnique())
            ->setTitre('Installation')
            ->setDescription('Positionner le patient')
            ->setLienImage('https://example.com/image.jpg')
            ->setOrdre(-1)
            ->setChirurgieModele(new ChirurgieModele());
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate($fiche);

        self::assertCount(2, $violations);
        self::assertSame(['lienImage', 'ordre'], $this->propertyPaths($violations));
    }

    public function testItAcceptsDescriptionOrUploadedImage(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $description = (new FicheTechnique())
            ->setTitre('Installation')
            ->setDescription('Positionner le patient')
            ->setOrdre(0)
            ->setChirurgieModele(new ChirurgieModele());
        $image = (new FicheTechnique())
            ->setTitre('Installation')
            ->setLienImage('/uploads/fiches-techniques/installation_1.jpg')
            ->setOrdre(0)
            ->setChirurgieModele(new ChirurgieModele());

        self::assertCount(0, $validator->validate($description));
        self::assertCount(0, $validator->validate($image));
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

    /**
     * @return list<string>
     */
    private function propertyPaths(\Symfony\Component\Validator\ConstraintViolationListInterface $violations): array
    {
        $paths = [];
        foreach ($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }
        sort($paths);

        return $paths;
    }
}
