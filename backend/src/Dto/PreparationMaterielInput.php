<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(expression: 'this.coche !== null or this.absent !== null', message: ErrorMessage::PREPARATION_STATE_REQUIRED)]
final readonly class PreparationMaterielInput
{
    // Initialise le DTO PreparationMaterielInput avec les valeurs décrivant la préparation du matériel. Dépendances utilisées : aucune dépendance de service directe.
    public function __construct(public ?bool $coche = null, public ?bool $absent = null)
    {
    }
}
