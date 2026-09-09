<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Utilisateur;
use App\Service\AuthenticatedUserProvider;
use App\Service\ReferenceDeletionGuard;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/** @implements ProcessorInterface<Utilisateur, void> */
final readonly class UtilisateurDeleteProcessor implements ProcessorInterface
{
    /** @param ProcessorInterface<object, void> $remove */
    public function __construct(#[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')] private ProcessorInterface $remove, private AuthenticatedUserProvider $authenticatedUser, private ReferenceDeletionGuard $guard)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($data === $this->authenticatedUser->getUser()) {
            throw new ConflictHttpException('Un administrateur ne peut pas supprimer son propre compte.');
        }
        $this->guard->assertCanDelete($data);
        $this->remove->process($data, $operation, $uriVariables, $context);
    }
}
