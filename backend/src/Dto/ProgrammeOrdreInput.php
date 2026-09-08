<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProgrammeOrdreInput
{
    /** @param list<int> $chirurgieIds */
    public function __construct(
        #[Assert\Count(min: 1, max: 100)]
        #[Assert\All([new Assert\Type('integer'), new Assert\Positive()])]
        public array $chirurgieIds = [],
    ) {
    }
}
