<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(expression: 'this.coche !== null or this.absent !== null', message: ErrorMessage::PREPARATION_STATE_REQUIRED)]
final readonly class PreparationMaterielInput
{
    public function __construct(public ?bool $coche = null, public ?bool $absent = null)
    {
    }
}
