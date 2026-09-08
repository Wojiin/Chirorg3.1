<?php

namespace App\Dto;

final readonly class ChirurgieVueFinale
{
    /**
     * @param array<string, mixed>|null  $validePar
     * @param array<string, mixed>       $chirurgien
     * @param array<string, mixed>       $chirurgieModele
     * @param list<array<string, mixed>> $materielsValides
     * @param list<array<string, mixed>> $ficheTechnique
     */
    public function __construct(public int $id, public string $dateProgrammee, public string $salle, public ?int $ordre, public bool $valide, public ?string $valideLe, public ?array $validePar, public array $chirurgien, public array $chirurgieModele, public array $materielsValides, public array $ficheTechnique)
    {
    }
}
