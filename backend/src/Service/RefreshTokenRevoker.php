<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Gesdinet\JWTRefreshTokenBundle\Model\RevokeRefreshTokenManagerInterface;

final readonly class RefreshTokenRevoker
{
    // Injecte le gestionnaire chargé de révoquer les jetons de renouvellement persistés pour un utilisateur. Dépendances utilisées : RevokeRefreshTokenManagerInterface.
    public function __construct(private RevokeRefreshTokenManagerInterface $refreshTokenManager)
    {
    }

    // Révoque tous les refresh tokens de l'utilisateur. Dépendances utilisées : RevokeRefreshTokenManagerInterface, Utilisateur.
    public function revokeFor(Utilisateur $utilisateur): void
    {
        $this->refreshTokenManager->revokeAllForUser($utilisateur);
    }
}
