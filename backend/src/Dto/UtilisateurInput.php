<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UtilisateurInput
{
    /** @param list<string>|null $roles */
    public function __construct(
        #[Assert\Email(message: ErrorMessage::EMAIL_INVALID)]
        #[Assert\Length(max: 180, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
        public ?string $email = null,
        #[Assert\All([new Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN'], message: ErrorMessage::ROLE_INVALID)])]
        public ?array $roles = null,
        public ?bool $actif = null,
        #[Assert\Length(min: 12, max: 4096, minMessage: ErrorMessage::TEXT_TOO_SHORT, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
        #[Assert\Regex(pattern: '/[a-z]/', message: ErrorMessage::PASSWORD_REQUIRES_LOWERCASE)]
        #[Assert\Regex(pattern: '/[A-Z]/', message: ErrorMessage::PASSWORD_REQUIRES_UPPERCASE)]
        #[Assert\Regex(pattern: '/\d/', message: ErrorMessage::PASSWORD_REQUIRES_DIGIT)]
        #[Assert\Regex(pattern: '/[^a-zA-Z\d]/', message: ErrorMessage::PASSWORD_REQUIRES_SPECIAL_CHARACTER)]
        public ?string $motDePasse = null,
    ) {
    }
}
