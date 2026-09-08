<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RefreshTokenRevoker
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function revokeFor(Utilisateur $utilisateur): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\RefreshToken token WHERE token.username = :username')
            ->setParameter('username', $utilisateur->getUserIdentifier())
            ->execute();
    }
}
