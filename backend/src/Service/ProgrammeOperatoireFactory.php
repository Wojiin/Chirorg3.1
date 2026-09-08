<?php

namespace App\Service;

use App\Dto\ProgrammeOperatoire;
use App\Entity\ChirurgiePlanifiee;

final class ProgrammeOperatoireFactory
{
    /** @param list<ChirurgiePlanifiee> $chirurgies */
    public function create(array $chirurgies): ?ProgrammeOperatoire
    {
        $first = $chirurgies[0] ?? null;
        $date = $first?->getDateProgrammee();
        $chirurgien = $first?->getChirurgien();
        $salle = $first?->getSalle();
        if (null === $first || null === $date || null === $chirurgien || null === $chirurgien->getId() || null === $salle) {
            return null;
        }
        $items = array_map(static fn (ChirurgiePlanifiee $item): array => [
            'id' => $item->getId(), 'ordre' => $item->getOrdre(), 'valide' => $item->isValide(),
            'chirurgieModele' => ['id' => $item->getChirurgieModele()?->getId(), 'intitule' => $item->getChirurgieModele()?->getIntitule()],
            'preparationsMateriel' => array_map(static fn ($p): array => ['id' => $p->getId(), 'coche' => $p->isCoche(), 'absent' => $p->isAbsent(), 'materiel' => ['id' => $p->getMateriel()?->getId(), 'intitule' => $p->getMateriel()?->getIntitule()]], $item->getPreparationsMateriel()->toArray()),
        ], $chirurgies);

        return new ProgrammeOperatoire(
            rawurlencode($date->format('Y-m-d').'|'.$salle.'|'.$chirurgien->getId()), $date->format('Y-m-d'), $salle,
            ['id' => $chirurgien->getId(), 'prenom' => (string) $chirurgien->getPrenom(), 'nom' => (string) $chirurgien->getNom()],
            count($items), count(array_filter($chirurgies, static fn (ChirurgiePlanifiee $item): bool => $item->isValide())), $items,
        );
    }
}
