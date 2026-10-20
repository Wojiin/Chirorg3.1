<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Ignore;

/** Expose le programme opératoire créé sans dupliquer les chirurgies planifiées qu'il contient. */
final readonly class ProgrammePlanificationOutput
{
    // Initialise le DTO ProgrammePlanificationOutput avec les valeurs décrivant le programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    private function __construct(#[Ignore] private ProgrammeOperatoire $programme)
    {
    }

    // Construit le résultat de planification à partir du programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    public static function fromProgramme(ProgrammeOperatoire $programme): self
    {
        return new self($programme);
    }

    // Retourne l'identifiant du programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    public function getId(): string
    {
        return $this->programme->id;
    }

    // Retourne date du programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    public function getDate(): string
    {
        return $this->programme->date;
    }

    // Retourne la salle opératoire du programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    public function getSalle(): string
    {
        return $this->programme->salle;
    }

    // Retourne le chirurgien du programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    /** @return array{id: int, prenom: string, nom: string} */
    public function getChirurgien(): array
    {
        return $this->programme->chirurgien;
    }

    // Retourne nombre chirurgies du programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    public function getNombreChirurgies(): int
    {
        return $this->programme->nombreChirurgies;
    }

    // Retourne nombre chirurgies validees du programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    public function getNombreChirurgiesValidees(): int
    {
        return $this->programme->nombreChirurgiesValidees;
    }

    // Retourne chirurgies du programme opératoire. Dépendances utilisées : ProgrammeOperatoire.
    /** @return list<array<string, mixed>> */
    public function getChirurgies(): array
    {
        return $this->programme->chirurgies;
    }
}
