<?php

namespace App\Dto;

use App\Error\ErrorMessage;
use App\Security\PasswordRequirements;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangementMotDePasseInput
{
    // Reçoit le mot de passe actuel et le nouveau mot de passe soumis par l'utilisateur, avec leurs contraintes de validation. Dépendances utilisées : Assert, ErrorMessage, PasswordRequirements.
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
