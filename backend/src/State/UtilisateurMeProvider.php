<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Utilisateur;
use App\Service\AuthenticatedUserProvider;

/** @implements ProviderInterface<Utilisateur> */
final readonly class UtilisateurMeProvider implements ProviderInterface
{
    public function __construct(private AuthenticatedUserProvider $authenticatedUser)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Utilisateur
    {
        return $this->authenticatedUser->getUser();
    }
}
