<?php

namespace App\Dto;

final readonly class ChirurgiePreparation
{
    /**
     * @param array<string, mixed>                                                       $chirurgien
     * @param array<string, mixed>                                                       $chirurgieModele
     * @param list<array<string, mixed>>                                                 $preparationsMateriel
     * @param array{total: int, coches: int, absents: int, traites: int, complete: bool} $progressionPreparation
     */
    public function __construct(public int $id, public string $dateProgrammee, public string $salle, public ?int $ordre, public int $nombreChirurgies, public bool $valide, public string $etatValidation, public array $chirurgien, public array $chirurgieModele, public array $preparationsMateriel, public array $progressionPreparation)
    {
    }
}
