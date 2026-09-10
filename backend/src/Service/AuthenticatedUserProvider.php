<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Error\ErrorMessage;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class AuthenticatedUserProvider
{
    public function __construct(private Security $security)
    {
    }

    public function getUser(): Utilisateur
    {
        $user = $this->security->getUser();

        return $user instanceof Utilisateur ? $user : throw new AccessDeniedException(ErrorMessage::AUTHENTICATED_USER_REQUIRED);
    }
}
