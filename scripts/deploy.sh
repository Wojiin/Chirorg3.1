#!/bin/sh
set -eu

project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
env_file=${ENV_FILE:-"$project_dir/.env.prod"}
image_tag=${1:-}
state_dir="$project_dir/.deploy"
previous_tag=

if [ ! -f "$env_file" ]; then
    echo "Copiez .env.prod.example vers .env.prod puis renseignez les secrets de production." >&2
    exit 1
fi

case "$image_tag" in
    sha-[0-9a-f][0-9a-f]*) ;;
    *) echo "Le tag d'image doit être immuable et commencer par sha-." >&2; exit 1 ;;
esac

mkdir -p "$state_dir"
export IMAGE_TAG="$image_tag"

compose() {
    docker compose --env-file "$env_file" -f "$project_dir/docker-compose.prod.yaml" "$@"
}

ENV_FILE="$env_file" "$project_dir/scripts/preflight.sh"

rollback_on_error() {
    status=$?
    trap - EXIT INT TERM
    if [ "$status" -ne 0 ] && [ -n "$previous_tag" ]; then
        echo "Échec du déploiement, retour applicatif vers $previous_tag..." >&2
        export IMAGE_TAG="$previous_tag"
        compose up -d --wait --wait-timeout 180 backend nginx frontend edge || true
    fi
    exit "$status"
}

trap rollback_on_error EXIT INT TERM

compose pull database backend nginx frontend edge
compose up -d --wait database

if [ -f "$state_dir/current-tag" ]; then
    previous_tag=$(sed -n '1p' "$state_dir/current-tag")
    backup_retention_days=$(sed -n 's/^BACKUP_RETENTION_DAYS=//p' "$env_file" | tail -n 1)
    backup_rclone_remote=$(sed -n 's/^BACKUP_RCLONE_REMOTE=//p' "$env_file" | tail -n 1)
    ENV_FILE="$env_file" \
        BACKUP_RETENTION_DAYS="${backup_retention_days:-14}" \
        BACKUP_RCLONE_REMOTE="$backup_rclone_remote" \
        "$project_dir/scripts/backup.sh"
    cp "$state_dir/current-tag" "$state_dir/previous-tag"
fi

compose run --rm backend php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
compose run --rm backend php bin/console doctrine:schema:validate
compose up -d --wait --wait-timeout 180
compose exec -T frontend wget --quiet --tries=1 --spider http://127.0.0.1:5173/

app_host=$(sed -n 's/^APP_HOST=//p' "$env_file" | tail -n 1)
case "$app_host" in
    ''|*[!A-Za-z0-9.-]*) echo "APP_HOST absent ou invalide dans $env_file." >&2; exit 1 ;;
esac
compose exec -T edge wget --quiet --tries=12 --timeout=10 --spider "https://$app_host/api/health"

printf '%s\n' "$image_tag" > "$state_dir/current-tag"
trap - EXIT INT TERM
echo "Version $image_tag déployée avec succès."
