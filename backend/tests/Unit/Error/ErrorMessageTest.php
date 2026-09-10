<?php

namespace App\Tests\Unit\Error;

use App\Error\ErrorMessage;
use PHPUnit\Framework\TestCase;

final class ErrorMessageTest extends TestCase
{
    public function testPublicMessagesAreNonEmptyAndUnique(): void
    {
        $messages = (new \ReflectionClass(ErrorMessage::class))->getConstants(\ReflectionClassConstant::IS_PUBLIC);

        self::assertGreaterThan(50, count($messages));
        self::assertSame(count($messages), count(array_unique($messages)));
        foreach ($messages as $message) {
            self::assertIsString($message);
            self::assertNotSame('', trim($message));
        }
    }

    public function testParameterizedMessagesAreFormattedByTheCatalog(): void
    {
        self::assertSame('Chirurgie modèle 42 introuvable.', ErrorMessage::chirurgieModeleNotFound(42));
        self::assertSame('Instances of "stdClass" are not supported.', ErrorMessage::unsupportedUser(\stdClass::class));
    }

    public function testCustomBackendErrorsAreNotDeclaredOutsideTheCatalog(): void
    {
        $sourceDirectory = dirname(__DIR__, 3).'/src';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourceDirectory));

        foreach ($files as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension() || 'ErrorMessage.php' === $file->getFilename()) {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            self::assertIsString($source);
            self::assertDoesNotMatchRegularExpression('/throw new [^(]+\(\s*[\'\"]/', $source, $file->getPathname());
            self::assertDoesNotMatchRegularExpression('/\b(?:message|minMessage|maxMessage):\s*[\'\"]/', $source, $file->getPathname());
            self::assertDoesNotMatchRegularExpression('/buildViolation\(\s*[\'\"]/', $source, $file->getPathname());
        }
    }
}
