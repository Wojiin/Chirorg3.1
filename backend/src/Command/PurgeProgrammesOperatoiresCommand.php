<?php

namespace App\Command;

use App\Repository\ChirurgiePlanifieeRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:programmes:purge-expired',
    description: 'Supprime les programmes opératoires dont la date est antérieure à aujourd’hui.',
)]
final class PurgeProgrammesOperatoiresCommand extends Command
{
    public function __construct(
        private readonly ChirurgiePlanifieeRepository $repository,
        private readonly ClockInterface $clock,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateLimite = \DateTimeImmutable::createFromInterface($this->clock->now())
            ->setTimezone(new \DateTimeZone('Europe/Paris'))
            ->setTime(0, 0);
        $nombreChirurgies = $this->repository->deleteProgrammesBefore($dateLimite);

        (new SymfonyStyle($input, $output))->success(sprintf(
            '%d chirurgie(s) de programme(s) antérieur(s) au %s supprimée(s).',
            $nombreChirurgies,
            $dateLimite->format('d/m/Y'),
        ));

        return Command::SUCCESS;
    }
}
