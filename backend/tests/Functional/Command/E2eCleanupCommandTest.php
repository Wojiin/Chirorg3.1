<?php

namespace App\Tests\Functional\Command;

use App\Command\E2eCleanupCommand;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class E2eCleanupCommandTest extends TestCase
{
    public function testItRefusesToRunInProduction(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::never())->method('transactional');
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('prod');

        $tester = new CommandTester(new E2eCleanupCommand($connection, $kernel));

        self::assertSame(1, $tester->execute([]));
        self::assertStringContainsString(
            'Le nettoyage E2E est interdit hors des environnements dev et test.',
            $tester->getDisplay(),
        );
    }
}
