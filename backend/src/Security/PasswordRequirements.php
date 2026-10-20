<?php

declare(strict_types=1);

namespace App\Security;

final class PasswordRequirements
{
    public const string PATTERN = '/\A(?=\S{12,4096}\z)(?=\S*[a-z])(?=\S*[A-Z])(?=\S*\d)(?=\S*[^a-zA-Z\d\s])\S+\z/D';

    // Empêche l'instanciation du catalogue statique des règles de complexité des mots de passe utilisateurs. Dépendances utilisées : aucune dépendance de service directe.
    private function __construct()
    {
    }
}
