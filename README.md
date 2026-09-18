# ChirOrg

ChirOrg est une application web de planification des programmes opératoires et de préparation du matériel. Elle permet de planifier les interventions, suivre chaque matériel comme prêt ou absent, effectuer une validation partielle ou finale et consulter les fiches techniques associées.

Le projet est réalisé dans le cadre du Titre Professionnel Concepteur Développeur d'Applications. Il ne stocke aucune donnée nominative de patient et ne remplace pas un logiciel médical certifié.

## Architecture

- Frontend : Vue 3, Vue Router, Pinia, Axios, Tailwind CSS et Reka UI.
- Backend : PHP 8.4, Symfony 8.1, API Platform 4.3 et Doctrine ORM.
- Données : MySQL 8.4 et migrations Doctrine.
- Sécurité : Symfony Security, JWT d'accès et refresh token HTTP-only.
- Qualité : PHPUnit, PHPStan, PHP CS Fixer, Vitest, ESLint, Playwright et Lighthouse.
- Exploitation : Docker Compose, Nginx, Caddy, GitHub Actions et GHCR.

## Installation de développement

Prérequis : Git, Docker Desktop ou Docker Engine avec le plugin Compose.

```bash
git clone https://github.com/Wojiin/Chirorg3.1.git
cd Chirorg3.1
docker compose --env-file .env.example -f docker-compose.dev.yaml up -d --build --wait
docker compose --env-file .env.example -f docker-compose.dev.yaml exec backend php bin/console doctrine:migrations:migrate --no-interaction
docker compose --env-file .env.example -f docker-compose.dev.yaml exec backend php bin/console doctrine:fixtures:load --no-interaction
```

L'interface est disponible sur `http://localhost:5173`, l'API sur `http://localhost:8080/api` et sa documentation sur `http://localhost:8080/api/docs`.

Arrêt de la pile :

```bash
docker compose --env-file .env.example -f docker-compose.dev.yaml down
```

L'option `--volumes` supprime également les données locales ; elle ne doit être utilisée que pour recréer volontairement une base vide.

## Vérifications

```bash
docker compose --env-file .env.example -f docker-compose.dev.yaml exec backend composer run quality
cd frontend && npm ci && npm run quality
cd frontend && npm run test:e2e
cd frontend && npm run lighthouse:ci
```

La CI exécute la qualité backend et frontend, la validation de la pile de production, les parcours de bout en bout, Lighthouse, les tests OWASP, un fuzzing OpenAPI borné et la détection de secrets.

## Sécurité et performance

Les tests fonctionnels de sécurité sont exécutés avec la suite backend et le scénario XSS avec Playwright. Le workflow `Audit de sécurité` complète ces contrôles par un fuzzing en lecture sur une base Docker éphémère.

Le workflow `Performance`, déclenchable manuellement et exécuté chaque semaine, lance le scénario k6 décrit dans `tests/load`. Il ne cible jamais automatiquement la production.

Les livrables CDA du candidat sont conservés localement dans `docs/` et volontairement exclus de Git, car ils sont mis en forme pour leur remise physique.

## Licence

Le code est déclaré `proprietary` dans Composer. Toute diffusion ou réutilisation nécessite l'autorisation du propriétaire du dépôt. Les dépendances conservent leurs licences respectives.
