<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ChirurgiePlanifiee;
use App\Service\AuthenticatedUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** @implements ProcessorInterface<ChirurgiePlanifiee, ChirurgiePlanifiee> */
final readonly class ChirurgieValidationProcessor implements ProcessorInterface
{
    public function __construct(private AuthenticatedUserProvider $authenticatedUser, private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ChirurgiePlanifiee
    {
        if ($data->isValide()) {
            return $data;
        }
        if ($data->getPreparationsMateriel()->isEmpty()) {
            throw new UnprocessableEntityHttpException('La chirurgie ne possède aucune préparation de matériel.');
        }
        $hasAbsent = false;
        foreach ($data->getPreparationsMateriel() as $preparation) {
            if (!$preparation->isCoche() && !$preparation->isAbsent()) {
                throw new UnprocessableEntityHttpException('Tout le matériel doit être déclaré prêt ou absent avant la validation.');
            }
            $hasAbsent = $hasAbsent || $preparation->isAbsent();
        }
        $user = $this->authenticatedUser->getUser();
        $now = new \DateTimeImmutable();
        $data->setModifieLe($now)->setModifiePar($user->getUserIdentifier());
        if (!$hasAbsent) {
            $data->setValide(true)->setValideLe($now)->setValidePar($user);
        }
        $this->entityManager->flush();

        return $data;
    }
}
