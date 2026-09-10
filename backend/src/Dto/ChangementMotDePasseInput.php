<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangementMotDePasseInput
{
    public function __construct(
        #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
        public string $motDePasseActuel = '',
        #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
        #[Assert\Length(min: 12, max: 4096, minMessage: ErrorMessage::TEXT_TOO_SHORT, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
        #[Assert\Regex(pattern: '/[a-z]/', message: ErrorMessage::NEW_PASSWORD_REQUIRES_LOWERCASE)]
        #[Assert\Regex(pattern: '/[A-Z]/', message: ErrorMessage::NEW_PASSWORD_REQUIRES_UPPERCASE)]
        #[Assert\Regex(pattern: '/\d/', message: ErrorMessage::NEW_PASSWORD_REQUIRES_DIGIT)]
        #[Assert\Regex(pattern: '/[^a-zA-Z\d]/', message: ErrorMessage::NEW_PASSWORD_REQUIRES_SPECIAL_CHARACTER)]
        public string $nouveauMotDePasse = '',
    ) {
    }
}
