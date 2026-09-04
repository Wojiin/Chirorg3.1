<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:utilisateur:create',
    description: 'Create an application user without exposing the password in process arguments.',
)]
final class UtilisateurCreateCommand extends Command
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email used to authenticate')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Grant ROLE_ADMIN')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailInput = $input->getArgument('email') ?? $io->ask('Email');
        if (!is_string($emailInput)) {
            $io->error('A valid email address is required.');

            return Command::INVALID;
        }

        $email = mb_strtolower(trim($emailInput));
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('A valid email address is required.');

            return Command::INVALID;
        }

        if (null !== $this->utilisateurRepository->findOneBy(['email' => $email])) {
            $io->error('An utilisateur already exists with this email address.');

            return Command::FAILURE;
        }

        $password = $io->askHidden('Password (12 characters minimum)');
        if (!is_string($password) || 12 > mb_strlen($password)) {
            $io->error('The password must contain at least 12 characters.');

            return Command::INVALID;
        }

        $utilisateur = (new Utilisateur())
            ->setEmail($email)
            ->setRoles($input->getOption('admin') ? ['ROLE_ADMIN'] : []);
        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $password));

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $io->success(sprintf('Utilisateur %s created.', $email));

        return Command::SUCCESS;
    }
}
