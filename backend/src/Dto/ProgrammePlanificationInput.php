<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProgrammePlanificationInput
{
    /** @param list<int> $chirurgieModeleIds */
    public function __construct(
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        #[Assert\NotNull]
        #[Assert\GreaterThanOrEqual('tomorrow Europe/Paris')]
        public ?\DateTimeImmutable $dateProgrammee = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public ?string $salle = null,
        #[Assert\NotNull]
        #[Assert\Positive]
        public ?int $chirurgienId = null,
        #[Assert\Count(min: 1, max: 50)]
        #[Assert\All([new Assert\Type('integer'), new Assert\Positive()])]
        public array $chirurgieModeleIds = [],
    ) {
    }
}
