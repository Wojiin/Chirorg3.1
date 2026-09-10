<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProgrammePlanificationInput
{
    /** @param list<int> $chirurgieModeleIds */
    public function __construct(
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
        #[Assert\GreaterThanOrEqual('tomorrow Europe/Paris', message: ErrorMessage::DATE_MUST_BE_TOMORROW_OR_LATER)]
        public ?\DateTimeImmutable $dateProgrammee = null,
        #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
        #[Assert\Length(max: 50, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
        public ?string $salle = null,
        #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
        #[Assert\Positive(message: ErrorMessage::POSITIVE_NUMBER_REQUIRED)]
        public ?int $chirurgienId = null,
        #[Assert\Count(min: 1, max: 50, minMessage: ErrorMessage::LIST_REQUIRES_ITEM, maxMessage: ErrorMessage::LIST_TOO_LONG)]
        #[Assert\All([new Assert\Type('integer', message: ErrorMessage::INTEGER_REQUIRED), new Assert\Positive(message: ErrorMessage::POSITIVE_NUMBER_REQUIRED)])]
        public array $chirurgieModeleIds = [],
    ) {
    }
}
