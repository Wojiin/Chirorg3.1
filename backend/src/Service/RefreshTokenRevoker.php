<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Gesdinet\JWTRefreshTokenBundle\Model\RevokeRefreshTokenManagerInterface;

final readonly class RefreshTokenRevoker
{
    public function __construct(private RevokeRefreshTokenManagerInterface $refreshTokenManager)
    {
    }

    public function revokeFor(Utilisateur $utilisateur): void
    {
        $this->refreshTokenManager->revokeAllForUser($utilisateur);
    }
}
