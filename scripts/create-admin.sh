#!/bin/sh
set -eu

project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
env_file=${ENV_FILE:-"$project_dir/.env.prod"}

if [ ! -f "$env_file" ]; then
    echo "Fichier d'environnement de production introuvable : $env_file" >&2
    exit 1
fi

if [ "$#" -gt 0 ]; then
    exec docker compose --env-file "$env_file" -f "$project_dir/docker-compose.prod.yaml" \
        exec backend php bin/console app:utilisateur:create --admin "$1"
fi

exec docker compose --env-file "$env_file" -f "$project_dir/docker-compose.prod.yaml" \
    exec backend php bin/console app:utilisateur:create --admin
