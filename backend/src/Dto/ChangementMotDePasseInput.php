<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangementMotDePasseInput
{
    public function __construct(
        #[Assert\NotBlank]
        public string $motDePasseActuel = '',
        #[Assert\NotBlank]
        #[Assert\Length(min: 12, max: 4096)]
        #[Assert\Regex(pattern: '/[a-z]/', message: 'Le nouveau mot de passe doit contenir une minuscule.')]
        #[Assert\Regex(pattern: '/[A-Z]/', message: 'Le nouveau mot de passe doit contenir une majuscule.')]
        #[Assert\Regex(pattern: '/\d/', message: 'Le nouveau mot de passe doit contenir un chiffre.')]
        #[Assert\Regex(pattern: '/[^a-zA-Z\d]/', message: 'Le nouveau mot de passe doit contenir un caractère spécial.')]
        public string $nouveauMotDePasse = '',
    ) {
    }
}
