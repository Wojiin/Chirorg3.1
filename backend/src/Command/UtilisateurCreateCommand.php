<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Security\PasswordRequirements;
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
    description: 'Crée un utilisateur sans exposer son mot de passe dans les arguments du processus.',
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
            ->addArgument('email', InputArgument::OPTIONAL, 'Adresse email utilisée pour se connecter')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Accorder le rôle administrateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailInput = $input->getArgument('email') ?? $io->ask('Email');
        if (!is_string($emailInput)) {
            $io->error('Une adresse email valide est obligatoire.');

            return Command::INVALID;
        }

        $email = mb_strtolower(trim($emailInput));
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Une adresse email valide est obligatoire.');

            return Command::INVALID;
        }

        if (null !== $this->utilisateurRepository->findOneBy(['email' => $email])) {
            $io->error('Un utilisateur existe déjà avec cette adresse email.');

            return Command::FAILURE;
        }

        $password = $io->askHidden('Mot de passe (12 caractères minimum)');
        if (!is_string($password) || !$this->isPasswordStrong($password)) {
            $io->error('Le mot de passe doit contenir entre 12 et 4096 caractères, avec une minuscule, une majuscule, un chiffre et un caractère spécial.');

            return Command::INVALID;
        }

        $utilisateur = (new Utilisateur())
            ->setEmail($email)
            ->setRoles($input->getOption('admin') ? ['ROLE_ADMIN'] : []);
        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $password));

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $io->success(sprintf('Utilisateur %s créé.', $email));

        return Command::SUCCESS;
    }

    private function isPasswordStrong(string $password): bool
    {
        $length = mb_strlen($password);

        return $length >= 12
            && $length <= 4096
            && 1 === preg_match(PasswordRequirements::PATTERN, $password);
    }
}
