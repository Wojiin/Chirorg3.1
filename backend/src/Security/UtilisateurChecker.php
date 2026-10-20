<?php

namespace App\Security;

use App\Entity\Utilisateur;
use App\Error\ErrorMessage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UtilisateurChecker implements UserCheckerInterface
{
    // Refuse la connexion lorsque le compte utilisateur est désactivé. Dépendances utilisées : DisabledException, ErrorMessage, UserInterface.
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Utilisateur && !$user->isActif()) {
            throw new DisabledException(ErrorMessage::ACCOUNT_DISABLED);
        }
    }

    // Conserve le point d'extension exécuté après l'authentification. Dépendances utilisées : TokenInterface, UserInterface.
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
