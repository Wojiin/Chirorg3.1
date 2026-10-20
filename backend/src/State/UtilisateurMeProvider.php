<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Utilisateur;
use App\Service\AuthenticatedUserProvider;

/** @implements ProviderInterface<Utilisateur> */
final readonly class UtilisateurMeProvider implements ProviderInterface
{
    // Injecte les services nécessaires au traitement de la session de l'utilisateur. Dépendances utilisées : AuthenticatedUserProvider.
    public function __construct(private AuthenticatedUserProvider $authenticatedUser)
    {
    }

    // Construit la représentation API de la session de l'utilisateur. Dépendances utilisées : AuthenticatedUserProvider, Operation.
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Utilisateur
    {
        return $this->authenticatedUser->getUser();
    }
}
