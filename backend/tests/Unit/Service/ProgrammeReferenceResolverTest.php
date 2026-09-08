<?php

namespace App\Tests\Unit\Service;

use App\Service\ProgrammeReferenceResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ProgrammeReferenceResolverTest extends TestCase
{
    public function testItResolvesAndNormalizesAProgrammeReference(): void
    {
        $reference = (new ProgrammeReferenceResolver())->resolve(['date' => '2030-01-15', 'salle' => ' Bloc 1 ', 'chirurgien' => '42']);
        self::assertSame('2030-01-15', $reference->date->format('Y-m-d'));
        self::assertSame('Bloc 1', $reference->salle);
        self::assertSame(42, $reference->chirurgienId);
    }

    public function testItRejectsAnImpossibleDate(): void
    {
        $this->expectException(BadRequestHttpException::class);
        (new ProgrammeReferenceResolver())->resolve(['date' => '2030-02-31', 'salle' => 'Bloc 1', 'chirurgien' => 1]);
    }

    public function testItRejectsAnInvalidSurgeonIdentifier(): void
    {
        $this->expectException(BadRequestHttpException::class);
        (new ProgrammeReferenceResolver())->resolve(['date' => '2030-01-15', 'salle' => 'Bloc 1', 'chirurgien' => 0]);
    }
}
