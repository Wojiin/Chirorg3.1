<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ProgrammeOperatoire;
use App\Dto\ProgrammeOrdreInput;
use App\Repository\ChirurgiePlanifieeRepository;
use App\Service\ProgrammeOperatoireService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** @implements ProcessorInterface<ProgrammeOrdreInput, ProgrammeOperatoire> */
final readonly class ProgrammeOrdreProcessor implements ProcessorInterface
{
    public function __construct(private ChirurgiePlanifieeRepository $repository, private ProgrammeOperatoireService $service, private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProgrammeOperatoire
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($uriVariables['date'] ?? ''));
        $salle = (string) ($uriVariables['salle'] ?? '');
        $chirurgien = (int) ($uriVariables['chirurgien'] ?? 0);
        if (false === $date || '' === $salle || $chirurgien < 1) {
            throw new BadRequestHttpException('Référence de programme invalide.');
        }
        $items = $this->repository->findProgrammes($date, $salle, $chirurgien);
        $indexed = [];
        foreach ($items as $item) {
            $indexed[$item->getId()] = $item;
        }
        if (count($indexed) !== count($data->chirurgieIds) || array_diff(array_keys($indexed), $data->chirurgieIds)) {
            throw new UnprocessableEntityHttpException('La permutation doit contenir exactement toutes les chirurgies du programme.');
        }
        foreach ($data->chirurgieIds as $position => $id) {
            $indexed[$id]->setOrdre($position + 1)->setModifieLe(new \DateTimeImmutable());
        }
        $this->entityManager->flush();

        return $this->service->one($date, $salle, $chirurgien) ?? throw new \LogicException('Le programme réordonné ne peut pas être relu.');
    }
}
