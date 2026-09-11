#!/bin/sh
set -eu

project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
env_file=${ENV_FILE:-"$project_dir/.env.prod"}
requested_dir=${1:-}

if [ "${RESTORE_CONFIRM:-}" != "RESTAURER_CHIRORG" ]; then
    echo "Restauration refusée. Définissez RESTORE_CONFIRM=RESTAURER_CHIRORG après vérification de la sauvegarde." >&2
    exit 1
fi

if [ -z "$requested_dir" ]; then
    echo "Indiquez le dossier de sauvegarde à restaurer." >&2
    exit 1
fi

backup_dir=$(realpath "$requested_dir")
backup_root=$(realpath "$project_dir/backups")
case "$backup_dir/" in
    "$backup_root"/*) ;;
    *) echo "La sauvegarde doit se trouver dans $backup_root." >&2; exit 1 ;;
esac

test -f "$backup_dir/base.sql"
test -f "$backup_dir/fichiers-techniques.tar.gz"

if [ -f "$backup_dir/SHA256SUMS" ]; then
    (cd "$backup_dir" && sha256sum --check SHA256SUMS)
fi

compose() {
    docker compose --env-file "$env_file" -f "$project_dir/docker-compose.prod.yaml" "$@"
}

compose stop edge frontend nginx backend
compose up -d --wait database
compose exec -T database sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < "$backup_dir/base.sql"
compose up -d --wait backend
compose exec -T backend tar -xzf - -C public < "$backup_dir/fichiers-techniques.tar.gz"
compose up -d --wait --wait-timeout 180

echo "Sauvegarde restaurée : $backup_dir"
