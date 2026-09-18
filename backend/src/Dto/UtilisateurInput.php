<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use App\Security\PasswordRequirements;
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
        #[Assert\Regex(pattern: PasswordRequirements::PATTERN, message: ErrorMessage::PASSWORD_REQUIREMENTS)]
        public ?string $motDePasse = null,
    ) {
    }
}
