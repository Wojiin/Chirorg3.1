<?php

namespace App\Tests\Integration\Entity;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\FicheTechnique;
use App\Entity\ListeMateriel;
use App\Entity\Materiel;
use App\Entity\Specialite;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EntityValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $validator = static::getContainer()->get(ValidatorInterface::class);
        self::assertInstanceOf(ValidatorInterface::class, $validator);
        $this->validator = $validator;
    }

    public function testSpecialiteRequiresALabelWithinDatabaseLength(): void
    {
        self::assertSame(['intitule'], $this->paths($this->validator->validate(new Specialite())));
        self::assertSame(
            ['intitule'],
            $this->paths($this->validator->validate((new Specialite())->setIntitule(str_repeat('a', 101)))),
        );
    }

    public function testChirurgienRequiresIdentityAndSpecialite(): void
    {
        self::assertSame(
            ['nom', 'prenom', 'specialite'],
            $this->paths($this->validator->validate(new Chirurgien())),
        );
    }

    public function testMaterielRequiresLabelAndSpecialiteAndLimitsOptionalFields(): void
    {
        $materiel = (new Materiel())
            ->setIntitule(str_repeat('a', 151))
            ->setAdresse(str_repeat('a', 151))
            ->setTypeMateriel(str_repeat('a', 101));

        self::assertSame(
            ['adresse', 'intitule', 'specialite', 'typeMateriel'],
            $this->paths($this->validator->validate($materiel)),
        );
    }

    public function testChirurgieModeleRequiresLabelAndSpecialite(): void
    {
        self::assertSame(
            ['intitule', 'specialite'],
            $this->paths($this->validator->validate(new ChirurgieModele())),
        );
    }

    public function testFicheTechniqueRequiresItsCompleteMinimumState(): void
    {
        self::assertSame(
            ['', 'chirurgieModele', 'ordre', 'titre'],
            $this->paths($this->validator->validate(new FicheTechnique())),
        );
    }

    public function testListeMaterielRequiresOwnersAndAtLeastOneMateriel(): void
    {
        self::assertSame(
            ['chirurgieModele', 'chirurgien', 'intitule', 'materiels'],
            $this->paths($this->validator->validate(new ListeMateriel())),
        );
    }

    public function testListeMaterielRejectsReferencesFromDifferentSpecialites(): void
    {
        $specialiteChirurgien = (new Specialite())->setIntitule('Cardiologie');
        $autreSpecialite = (new Specialite())->setIntitule('Urologie');
        $liste = (new ListeMateriel())
            ->setIntitule('Liste pontage')
            ->setChirurgien(
                (new Chirurgien())->setPrenom('Claire')->setNom('Martin')->setSpecialite($specialiteChirurgien),
            )
            ->setChirurgieModele(
                (new ChirurgieModele())->setIntitule('Pontage')->setSpecialite($autreSpecialite),
            )
            ->addMateriel((new Materiel())->setIntitule('Clamp')->setSpecialite($autreSpecialite));

        self::assertSame(
            ['chirurgieModele', 'materiels'],
            $this->paths($this->validator->validate($liste)),
        );
    }

    public function testListeMaterielAcceptsACompleteConsistentState(): void
    {
        $specialite = (new Specialite())->setIntitule('Cardiologie');
        $liste = (new ListeMateriel())
            ->setIntitule('Liste pontage')
            ->setChirurgien(
                (new Chirurgien())->setPrenom('Claire')->setNom('Martin')->setSpecialite($specialite),
            )
            ->setChirurgieModele(
                (new ChirurgieModele())->setIntitule('Pontage')->setSpecialite($specialite),
            )
            ->addMateriel((new Materiel())->setIntitule('Clamp')->setSpecialite($specialite));

        self::assertCount(0, $this->validator->validate($liste));
    }

    public function testUtilisateurRequiresAValidEmailWithinDatabaseLength(): void
    {
        self::assertSame(
            ['email'],
            $this->paths($this->validator->validate((new Utilisateur())->setEmail('invalid-email'))),
        );
        self::assertSame(
            ['email', 'email'],
            $this->paths($this->validator->validate((new Utilisateur())->setEmail(str_repeat('a', 181)))),
        );
    }

    /**
     * @return list<string>
     */
    private function paths(ConstraintViolationListInterface $violations): array
    {
        $paths = [];
        foreach ($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }
        sort($paths);

        return $paths;
    }
}
