<?php

namespace App\Tests\Functional\Command;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UtilisateurCreateCommandTest extends KernelTestCase
{
    public function testItCreatesAnAdminWithAHashedPassword(): void
    {
        self::bootKernel();
        $email = 'command-'.bin2hex(random_bytes(5)).'@example.com';
        $plainPassword = 'Strong-password-42!';
        $tester = $this->tester();
        $tester->setInputs([$plainPassword]);

        $status = $tester->execute(['email' => strtoupper($email), '--admin' => true]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('créé', $tester->getDisplay());
        $repository = static::getContainer()->get(UtilisateurRepository::class);
        $utilisateur = $repository->findOneBy(['email' => $email]);
        self::assertInstanceOf(Utilisateur::class, $utilisateur);
        self::assertContains('ROLE_ADMIN', $utilisateur->getRoles());
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($utilisateur, $plainPassword));
        self::assertNotSame($plainPassword, $utilisateur->getPassword());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->remove($utilisateur);
        $entityManager->flush();
    }

    public function testItRejectsInvalidEmailAndShortPassword(): void
    {
        self::bootKernel();
        $invalidEmailTester = $this->tester();
        self::assertSame(Command::INVALID, $invalidEmailTester->execute(['email' => 'invalid-email']));

        $shortPasswordTester = $this->tester();
        $shortPasswordTester->setInputs(['too-short']);
        self::assertSame(
            Command::INVALID,
            $shortPasswordTester->execute(['email' => 'valid@example.com']),
        );
    }

    private function tester(): CommandTester
    {
        $kernel = self::$kernel;
        if (null === $kernel) {
            throw new \LogicException('The test kernel must be booted before creating the command tester.');
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);

        return new CommandTester($application->find('app:utilisateur:create'));
    }
}
