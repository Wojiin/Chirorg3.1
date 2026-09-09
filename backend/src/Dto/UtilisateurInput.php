<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UtilisateurInput
{
    /** @param list<string>|null $roles */
    public function __construct(
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public ?string $email = null,
        #[Assert\All([new Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN'])])]
        public ?array $roles = null,
        public ?bool $actif = null,
        #[Assert\Length(min: 12, max: 4096)]
        #[Assert\Regex(pattern: '/[a-z]/', message: 'Le mot de passe doit contenir une minuscule.')]
        #[Assert\Regex(pattern: '/[A-Z]/', message: 'Le mot de passe doit contenir une majuscule.')]
        #[Assert\Regex(pattern: '/\d/', message: 'Le mot de passe doit contenir un chiffre.')]
        #[Assert\Regex(pattern: '/[^a-zA-Z\d]/', message: 'Le mot de passe doit contenir un caractère spécial.')]
        public ?string $motDePasse = null,
    ) {
    }
}
