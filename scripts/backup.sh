#!/bin/sh
set -eu

project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
env_file=${ENV_FILE:-"$project_dir/.env.prod"}
timestamp=$(date -u +%Y%m%dT%H%M%SZ)
backup_dir="$project_dir/backups/$timestamp"

if [ ! -f "$env_file" ]; then
    echo "Fichier d'environnement de production introuvable : $env_file" >&2
    exit 1
fi

mkdir -p "$backup_dir"

compose() {
    docker compose --env-file "$env_file" -f "$project_dir/docker-compose.prod.yaml" "$@"
}

compose exec -T database sh -c \
    'exec mysqldump --single-transaction --routines --triggers -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' \
    > "$backup_dir/base.sql"
compose exec -T backend tar -czf - -C public uploads > "$backup_dir/fichiers-techniques.tar.gz"

if command -v sha256sum >/dev/null 2>&1; then
    (cd "$backup_dir" && sha256sum base.sql fichiers-techniques.tar.gz > SHA256SUMS)
fi

echo "Sauvegarde créée : $backup_dir"
