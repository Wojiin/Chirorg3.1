<?php

declare(strict_types=1);

namespace App\Security;

final class PasswordRequirements
{
    public const string PATTERN = '/\A(?=\S{12,4096}\z)(?=\S*[a-z])(?=\S*[A-Z])(?=\S*\d)(?=\S*[^a-zA-Z\d\s])\S+\z/D';

    private function __construct()
    {
    }
}
