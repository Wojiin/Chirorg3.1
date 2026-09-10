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
    public function __construct(private AuthenticatedUserProvider $authenticatedUser, private RefreshTokenRevoker $refreshTokenRevoker, private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager)
    {
    }

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
