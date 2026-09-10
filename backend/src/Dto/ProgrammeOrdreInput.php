<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProgrammeOrdreInput
{
    /** @param list<int> $chirurgieIds */
    public function __construct(
        #[Assert\Count(min: 1, max: 100, minMessage: ErrorMessage::LIST_REQUIRES_ITEM, maxMessage: ErrorMessage::LIST_TOO_LONG)]
        #[Assert\All([new Assert\Type('integer', message: ErrorMessage::INTEGER_REQUIRED), new Assert\Positive(message: ErrorMessage::POSITIVE_NUMBER_REQUIRED)])]
        public array $chirurgieIds = [],
    ) {
    }
}
