<?php

namespace App\Dto;

final readonly class ProgrammeReference
{
    public function __construct(public \DateTimeImmutable $date, public string $salle, public int $chirurgienId)
    {
    }
}
