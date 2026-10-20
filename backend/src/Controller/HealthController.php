<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class HealthController
{
    // Injecte la connexion Doctrine utilisée pour vérifier la disponibilité de la base de données. Dépendances utilisées : Connection.
    public function __construct(private Connection $connection)
    {
    }

    #[Route('/api/health', name: 'api_health', methods: ['GET'])]
    // Retourne l'état de santé de l'API après avoir vérifié que la base de données répond à une requête minimale. Dépendances utilisées : Connection, JsonResponse, Response.
    public function __invoke(): JsonResponse
    {
        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Throwable) {
            return new JsonResponse(
                ['etat' => 'indisponible'],
                Response::HTTP_SERVICE_UNAVAILABLE,
            );
        }

        return new JsonResponse(['etat' => 'disponible']);
    }
}
