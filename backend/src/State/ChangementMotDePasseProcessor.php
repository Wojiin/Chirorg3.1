<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ChangementMotDePasseInput;
use App\Entity\Utilisateur;
use App\Error\ErrorMessage;
use App\Service\AuthenticatedUserProvider;
use App\Service\RefreshTokenRevoker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @implements ProcessorInterface<ChangementMotDePasseInput, Utilisateur> */
final readonly class ChangementMotDePasseProcessor implements ProcessorInterface
{
    // Injecte les services qui identifient l'utilisateur, vérifient son mot de passe, révoquent ses sessions et enregistrent le nouveau secret. Dépendances utilisées : AuthenticatedUserProvider, EntityManagerInterface, RefreshTokenRevoker, UserPasswordHasherInterface.
    public function __construct(private AuthenticatedUserProvider $authenticatedUser, private RefreshTokenRevoker $refreshTokenRevoker, private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager)
    {
    }

    // Vérifie le mot de passe actuel, refuse sa réutilisation, enregistre le nouveau mot de passe puis révoque les sessions de l'utilisateur. Dépendances utilisées : AuthenticatedUserProvider, EntityManagerInterface, ErrorMessage, Operation, RefreshTokenRevoker, UnprocessableEntityHttpException, UserPasswordHasherInterface.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Utilisateur
    {
        $utilisateur = $this->authenticatedUser->getUser();
        if (!$this->passwordHasher->isPasswordValid($utilisateur, $data->motDePasseActuel)) {
            throw new UnprocessableEntityHttpException(ErrorMessage::CURRENT_PASSWORD_INCORRECT);
        }
        if ($this->passwordHasher->isPasswordValid($utilisateur, $data->nouveauMotDePasse)) {
            throw new UnprocessableEntityHttpException(ErrorMessage::NEW_PASSWORD_MUST_DIFFER);
        }
        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $data->nouveauMotDePasse));
        $this->refreshTokenRevoker->revokeFor($utilisateur);
        $this->entityManager->flush();

        return $utilisateur;
    }
}
