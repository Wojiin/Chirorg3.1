<?php

namespace App\Dto;

final readonly class ProgrammeReference
{
    // Initialise le DTO ProgrammeReference avec les valeurs décrivant le programme opératoire. Dépendances utilisées : aucune dépendance de service directe.
    public function __construct(public \DateTimeImmutable $date, public string $salle, public int $chirurgienId)
    {
    }
}
