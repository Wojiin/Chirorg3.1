<?php

namespace App\Service;

use App\Dto\ProgrammeReference;
use App\Error\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ProgrammeReferenceResolver
{
    /** @param array<string, mixed> $uriVariables */
    public function resolve(array $uriVariables): ProgrammeReference
    {
        $chirurgien = filter_var($uriVariables['chirurgien'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $salle = trim((string) ($uriVariables['salle'] ?? ''));
        if (false === $chirurgien || '' === $salle) {
            throw new BadRequestHttpException(ErrorMessage::PROGRAMME_REFERENCE_REQUIRED);
        }

        return new ProgrammeReference($this->date((string) ($uriVariables['date'] ?? '')), $salle, $chirurgien);
    }

    public function date(string $value): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (false === $date || $date->format('Y-m-d') !== $value) {
            throw new BadRequestHttpException(ErrorMessage::DATE_FORMAT_INVALID);
        }

        return $date;
    }
}
