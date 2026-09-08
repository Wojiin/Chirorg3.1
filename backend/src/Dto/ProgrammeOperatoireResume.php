<?php

namespace App\Dto;

final readonly class ProgrammeOperatoireResume
{
    /**
     * @param array{id: int, prenom: string, nom: string}                                $chirurgien
     * @param array{total: int, coches: int, absents: int, traites: int, complete: bool} $progressionPreparation
     */
    public function __construct(public string $date, public string $salle, public array $chirurgien, public int $nombreChirurgies, public int $nombreChirurgiesValidees, public array $progressionPreparation, public ?string $creePar)
    {
    }
}
