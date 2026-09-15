#!/bin/sh
set -eu

project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
env_file=${ENV_FILE:-"$project_dir/.env.prod"}
timestamp=$(date -u +%Y%m%dT%H%M%SZ)
backup_dir="$project_dir/backups/$timestamp"
retention_days=${BACKUP_RETENTION_DAYS:-14}
remote=${BACKUP_RCLONE_REMOTE:-}

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

if [ -f "$project_dir/.deploy/current-tag" ]; then
    cp "$project_dir/.deploy/current-tag" "$backup_dir/image-tag"
fi

if command -v sha256sum >/dev/null 2>&1; then
    (cd "$backup_dir" && sha256sum base.sql fichiers-techniques.tar.gz > SHA256SUMS)
fi

case "$retention_days" in
    ''|*[!0-9]*) echo "BACKUP_RETENTION_DAYS doit être un nombre entier." >&2; exit 1 ;;
esac

find "$project_dir/backups" -mindepth 1 -maxdepth 1 -type d -mtime "+$retention_days" -exec rm -rf -- {} +

if [ -n "$remote" ]; then
    if ! command -v rclone >/dev/null 2>&1; then
        echo "BACKUP_RCLONE_REMOTE est défini mais rclone n'est pas installé." >&2
        exit 1
    fi
    rclone copy "$backup_dir" "${remote%/}/$timestamp" --checksum
fi

echo "Sauvegarde créée : $backup_dir"
