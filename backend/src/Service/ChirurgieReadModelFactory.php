<?php

namespace App\Service;

use App\Dto\ChirurgiePreparation;
use App\Dto\ChirurgieVueFinale;
use App\Entity\ChirurgiePlanifiee;
use App\Entity\FicheTechnique;
use App\Entity\PreparationMateriel;

final readonly class ChirurgieReadModelFactory
{
    public function __construct(private PreparationProgressCalculator $progressCalculator)
    {
    }

    public function createPreparation(ChirurgiePlanifiee $chirurgie): ChirurgiePreparation
    {
        $progress = $this->progressCalculator->calculate($chirurgie->getPreparationsMateriel());

        return new ChirurgiePreparation($chirurgie->getId() ?? 0, $chirurgie->getDateProgrammee()?->format('Y-m-d') ?? '', $chirurgie->getSalle() ?? '', $chirurgie->getOrdre(), $chirurgie->isValide(), $this->progressCalculator->validationState($chirurgie->isValide(), $progress), $this->surgeonData($chirurgie), $this->modelData($chirurgie), $this->preparationRows($chirurgie), $progress);
    }

    public function createFinalView(ChirurgiePlanifiee $chirurgie): ChirurgieVueFinale
    {
        $user = $chirurgie->getValidePar();

        return new ChirurgieVueFinale($chirurgie->getId() ?? 0, $chirurgie->getDateProgrammee()?->format('Y-m-d') ?? '', $chirurgie->getSalle() ?? '', $chirurgie->getOrdre(), $chirurgie->isValide(), $chirurgie->getValideLe()?->format(\DateTimeInterface::ATOM), null === $user ? null : ['id' => $user->getId(), 'email' => $user->getEmail()], $this->surgeonData($chirurgie), $this->modelData($chirurgie), $this->validatedMaterialRows($chirurgie), $this->technicalSheetRows($chirurgie));
    }

    /** @return array<string, mixed> */
    public function createProgrammeItem(ChirurgiePlanifiee $chirurgie, bool $technicalSheets = false): array
    {
        $progress = $this->progressCalculator->calculate($chirurgie->getPreparationsMateriel());
        $data = ['id' => $chirurgie->getId(), 'dateProgrammee' => $chirurgie->getDateProgrammee()?->format('Y-m-d'), 'ordre' => $chirurgie->getOrdre(), 'valide' => $chirurgie->isValide(), 'etatValidation' => $this->progressCalculator->validationState($chirurgie->isValide(), $progress), 'valideLe' => $chirurgie->getValideLe()?->format(\DateTimeInterface::ATOM), 'creeLe' => $chirurgie->getCreeLe()->format(\DateTimeInterface::ATOM), 'creePar' => $chirurgie->getCreePar(), 'modifieLe' => $chirurgie->getModifieLe()->format(\DateTimeInterface::ATOM), 'modifiePar' => $chirurgie->getModifiePar(), 'chirurgieModele' => $this->modelData($chirurgie), 'progressionPreparation' => $progress, 'preparationsMateriel' => $this->preparationRows($chirurgie)];
        if ($technicalSheets) {
            $data['fichesTechniques'] = $this->technicalSheetRows($chirurgie);
        }

        return $data;
    }

    /** @return array{id: int, prenom: string, nom: string} */
    public function surgeonData(ChirurgiePlanifiee $chirurgie): array
    {
        $surgeon = $chirurgie->getChirurgien();

        return ['id' => $surgeon?->getId() ?? 0, 'prenom' => $surgeon?->getPrenom() ?? '', 'nom' => $surgeon?->getNom() ?? ''];
    }

    /** @return array{id: int|null, intitule: string|null} */
    private function modelData(ChirurgiePlanifiee $chirurgie): array
    {
        return ['id' => $chirurgie->getChirurgieModele()?->getId(), 'intitule' => $chirurgie->getChirurgieModele()?->getIntitule()];
    }

    /** @return list<array<string, mixed>> */
    private function preparationRows(ChirurgiePlanifiee $chirurgie): array
    {
        return array_values(array_map(static function (PreparationMateriel $preparation): array {
            $materiel = $preparation->getMateriel();

            return ['id' => $preparation->getId(), 'coche' => $preparation->isCoche(), 'absent' => $preparation->isAbsent(), 'cocheLe' => $preparation->getCocheLe()?->format(\DateTimeInterface::ATOM), 'materiel' => ['id' => $materiel?->getId(), 'intitule' => $materiel?->getIntitule(), 'adresse' => $materiel?->getAdresse(), 'typeMateriel' => $materiel?->getTypeMateriel()]];
        }, $chirurgie->getPreparationsMateriel()->toArray()));
    }

    /** @return list<array<string, mixed>> */
    private function validatedMaterialRows(ChirurgiePlanifiee $chirurgie): array
    {
        $rows = [];
        foreach ($chirurgie->getPreparationsMateriel() as $preparation) {
            if (!$preparation->isCoche()) {
                continue;
            } $materiel = $preparation->getMateriel();
            $rows[] = ['id' => $materiel?->getId(), 'intitule' => $materiel?->getIntitule(), 'adresse' => $materiel?->getAdresse(), 'typeMateriel' => $materiel?->getTypeMateriel(), 'cocheLe' => $preparation->getCocheLe()?->format(\DateTimeInterface::ATOM)];
        }

        return $rows;
    }

    /** @return list<array<string, mixed>> */
    private function technicalSheetRows(ChirurgiePlanifiee $chirurgie): array
    {
        $sheets = $chirurgie->getChirurgieModele()?->getFichesTechniques()->toArray() ?? [];
        usort($sheets, static fn (FicheTechnique $a, FicheTechnique $b): int => [$a->getOrdre() ?? PHP_INT_MAX, $a->getId() ?? PHP_INT_MAX] <=> [$b->getOrdre() ?? PHP_INT_MAX, $b->getId() ?? PHP_INT_MAX]);

        return array_map(static fn (FicheTechnique $sheet): array => ['id' => $sheet->getId(), 'titre' => $sheet->getTitre(), 'description' => $sheet->getDescription(), 'lienImage' => $sheet->getLienImage(), 'ordre' => $sheet->getOrdre()], $sheets);
    }
}
