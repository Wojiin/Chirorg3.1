<?php

namespace App\Dto;

final readonly class ProgrammePlanificationOutput
{
    /**
     * @param array{id: int, prenom: string, nom: string} $chirurgien
     * @param list<array<string, mixed>>                  $chirurgies
     */
    public function __construct(
        public string $id,
        public string $date,
        public string $salle,
        public array $chirurgien,
        public int $nombreChirurgies,
        public int $nombreChirurgiesValidees,
        public array $chirurgies,
    ) {
    }

    public static function fromProgramme(ProgrammeOperatoire $programme): self
    {
        return new self($programme->id, $programme->date, $programme->salle, $programme->chirurgien, $programme->nombreChirurgies, $programme->nombreChirurgiesValidees, $programme->chirurgies);
    }
}
