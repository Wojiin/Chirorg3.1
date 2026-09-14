<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Ignore;

/** Adapte la ressource créée en sortie non-ressource, sans dupliquer son état. */
final readonly class ProgrammePlanificationOutput
{
    private function __construct(#[Ignore] private ProgrammeOperatoire $programme)
    {
    }

    public static function fromProgramme(ProgrammeOperatoire $programme): self
    {
        return new self($programme);
    }

    public function getId(): string
    {
        return $this->programme->id;
    }

    public function getDate(): string
    {
        return $this->programme->date;
    }

    public function getSalle(): string
    {
        return $this->programme->salle;
    }

    /** @return array{id: int, prenom: string, nom: string} */
    public function getChirurgien(): array
    {
        return $this->programme->chirurgien;
    }

    public function getNombreChirurgies(): int
    {
        return $this->programme->nombreChirurgies;
    }

    public function getNombreChirurgiesValidees(): int
    {
        return $this->programme->nombreChirurgiesValidees;
    }

    /** @return list<array<string, mixed>> */
    public function getChirurgies(): array
    {
        return $this->programme->chirurgies;
    }
}
