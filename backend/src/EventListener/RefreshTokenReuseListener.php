<?php

namespace App\EventListener;

use Gesdinet\JWTRefreshTokenBundle\Event\RefreshTokenReuseDetectedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'gesdinet.refresh_token_reuse_detected')]
final readonly class RefreshTokenReuseListener
{
    // Injecte le journal de sécurité dédié aux alertes de réutilisation des jetons de renouvellement. Dépendances utilisées : Autowire, LoggerInterface.
    public function __construct(
        #[Autowire(service: 'monolog.logger.security_alert')]
        private LoggerInterface $logger,
    ) {
    }

    // Journalise l'utilisateur, le nombre de sessions révoquées et l'adresse IP lorsqu'un jeton de renouvellement déjà consommé est réutilisé. Dépendances utilisées : LoggerInterface, RefreshTokenReuseDetectedEvent.
    public function __invoke(RefreshTokenReuseDetectedEvent $event): void
    {
        $this->logger->warning('Réutilisation d’un refresh token déjà consommé.', [
            'utilisateur' => $event->getSpentToken()->username,
            'sessions_revoquees' => $event->getRevokedTokens(),
            'adresse_ip' => $event->getRequest()->getClientIp(),
        ]);
    }
}
