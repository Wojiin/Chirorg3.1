<?php

namespace App\Tests\Unit\Service;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\Materiel;
use App\Entity\Specialite;
use App\Repository\SpecialiteRepository;
use App\Service\SpecialiteDeletionService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class SpecialiteDeletionServiceTest extends TestCase
{
    public function testItReassignsEveryReferenceToTheDefaultSpecialite(): void
    {
        $defaultSpecialite = (new Specialite())->setIntitule(Specialite::SANS_SPECIALITE);
        $specialite = (new Specialite())->setIntitule('Orthopédie');
        $chirurgien = new Chirurgien();
        $materiel = new Materiel();
        $chirurgieModele = new ChirurgieModele();
        $specialite->addChirurgien($chirurgien);
        $specialite->addMateriel($materiel);
        $specialite->addChirurgieModele($chirurgieModele);

        $repository = $this->createMock(SpecialiteRepository::class);
        $repository->expects(self::once())->method('findDefault')->willReturn($defaultSpecialite);

        (new SpecialiteDeletionService($repository))->reassignReferences($specialite);

        self::assertSame($defaultSpecialite, $chirurgien->getSpecialite());
        self::assertSame($defaultSpecialite, $materiel->getSpecialite());
        self::assertSame($defaultSpecialite, $chirurgieModele->getSpecialite());
    }

    public function testItProtectsTheDefaultSpecialite(): void
    {
        $repository = $this->createMock(SpecialiteRepository::class);
        $repository->expects(self::never())->method('findDefault');

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessageIs('La spécialité « Sans spécialité » ne peut pas être supprimée.');

        (new SpecialiteDeletionService($repository))->reassignReferences(
            (new Specialite())->setIntitule(Specialite::SANS_SPECIALITE),
        );
    }

    public function testItFailsExplicitlyWhenTheDefaultSpecialiteIsMissing(): void
    {
        $repository = $this->createMock(SpecialiteRepository::class);
        $repository->expects(self::once())->method('findDefault')->willReturn(null);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs('La spécialité « Sans spécialité » est absente.');

        (new SpecialiteDeletionService($repository))->reassignReferences(
            (new Specialite())->setIntitule('Orthopédie'),
        );
    }
}
