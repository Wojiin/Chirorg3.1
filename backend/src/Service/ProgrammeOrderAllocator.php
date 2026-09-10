<?php

namespace App\Service;

use App\Entity\Chirurgien;
use App\Error\ErrorMessage;
use App\Repository\ChirurgiePlanifieeRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ProgrammeOrderAllocator
{
    public function __construct(private ChirurgiePlanifieeRepository $repository, private EntityManagerInterface $entityManager)
    {
    }

    public function reserveNextOrder(\DateTimeInterface $date, string $salle, Chirurgien $chirurgien): int
    {
        if (null === $chirurgien->getId()) {
            throw new \LogicException(ErrorMessage::CHIRURGIEN_MUST_BE_PERSISTED);
        }
        $this->entityManager->lock($chirurgien, LockMode::PESSIMISTIC_WRITE);

        return $this->repository->nextOrder($date, trim($salle), $chirurgien);
    }
}
