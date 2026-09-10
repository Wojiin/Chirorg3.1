<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\UtilisateurInput;
use App\Entity\Utilisateur;
use App\Error\ErrorMessage;
use App\Repository\UtilisateurRepository;
use App\Service\AuthenticatedUserProvider;
use App\Service\RefreshTokenRevoker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @implements ProcessorInterface<UtilisateurInput, Utilisateur> */
final readonly class UtilisateurWriteProcessor implements ProcessorInterface
{
    public function __construct(private UtilisateurRepository $repository, private AuthenticatedUserProvider $authenticatedUser, private RefreshTokenRevoker $refreshTokenRevoker, private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Utilisateur
    {
        $creating = $operation instanceof Post;
        $utilisateur = $creating
            ? new Utilisateur()
            : ($this->repository->find((int) ($uriVariables['id'] ?? 0)) ?? throw new NotFoundHttpException(ErrorMessage::UTILISATEUR_NOT_FOUND));

        if ($creating && (null === $data->email || null === $data->motDePasse)) {
            throw new BadRequestHttpException(ErrorMessage::CREDENTIALS_REQUIRED);
        }
        if (null !== $data->email) {
            $existing = $this->repository->findOneBy(['email' => mb_strtolower(trim($data->email))]);
            if (null !== $existing && $existing !== $utilisateur) {
                throw new ConflictHttpException(ErrorMessage::EMAIL_ALREADY_USED);
            }
            $utilisateur->setEmail($data->email);
        }
        if (!$creating && $utilisateur === $this->authenticatedUser->getUser()) {
            if (false === $data->actif || (null !== $data->roles && !in_array('ROLE_ADMIN', $data->roles, true))) {
                throw new ConflictHttpException(ErrorMessage::ADMIN_SELF_UPDATE_FORBIDDEN);
            }
        }
        if (null !== $data->roles) {
            $utilisateur->setRoles(array_values(array_unique($data->roles)));
        }
        if (null !== $data->actif) {
            $utilisateur->setActif($data->actif);
        }
        if (null !== $data->motDePasse) {
            $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $data->motDePasse));
        }

        if (!$creating && (false === $data->actif || null !== $data->motDePasse)) {
            $this->refreshTokenRevoker->revokeFor($utilisateur);
        }

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        return $utilisateur;
    }
}
