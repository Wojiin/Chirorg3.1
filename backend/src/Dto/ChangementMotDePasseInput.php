<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use App\Security\PasswordRequirements;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangementMotDePasseInput
{
    public function __construct(
        #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
        public string $motDePasseActuel = '',
        #[Assert\Sequentially([
            new Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD),
            new Assert\Regex(pattern: PasswordRequirements::PATTERN, message: ErrorMessage::NEW_PASSWORD_REQUIREMENTS),
        ])]
        public string $nouveauMotDePasse = '',
    ) {
    }
}
