<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(expression: 'this.coche !== null or this.absent !== null', message: 'Un état prêt ou absent doit être fourni.')]
final readonly class PreparationMaterielInput
{
    public function __construct(public ?bool $coche = null, public ?bool $absent = null)
    {
    }
}
