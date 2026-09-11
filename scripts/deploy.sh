#!/bin/sh
set -eu

project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
env_file=${ENV_FILE:-"$project_dir/.env.prod"}
image_tag=${1:-}
state_dir="$project_dir/.deploy"

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

compose pull database backend nginx frontend edge
compose up -d --wait database

if [ -f "$state_dir/current-tag" ]; then
    ENV_FILE="$env_file" "$project_dir/scripts/backup.sh"
    cp "$state_dir/current-tag" "$state_dir/previous-tag"
fi

compose run --rm backend php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
compose up -d --wait --wait-timeout 180
compose exec -T frontend wget --quiet --tries=1 --spider http://127.0.0.1:5173/

printf '%s\n' "$image_tag" > "$state_dir/current-tag"
echo "Version $image_tag déployée avec succès."
