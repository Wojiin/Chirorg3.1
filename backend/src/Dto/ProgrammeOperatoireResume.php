<?php

namespace App\Dto;

final readonly class ProgrammeOperatoireResume
{
    // Initialise le DTO ProgrammeOperatoireResume avec les valeurs décrivant le programme opératoire. Dépendances utilisées : aucune dépendance de service directe.
    /**
     * @param array{id: int, prenom: string, nom: string}                                $chirurgien
     * @param array{total: int, coches: int, absents: int, traites: int, complete: bool} $progressionPreparation
     */
    public function __construct(public string $id, public string $date, public string $salle, public array $chirurgien, public int $nombreChirurgies, public int $nombreChirurgiesValidees, public array $progressionPreparation, public ?string $creePar)
    {
    }
}
