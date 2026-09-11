#!/bin/sh
set -eu

project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
env_file=${ENV_FILE:-"$project_dir/.env.prod"}
state_dir="$project_dir/.deploy"

if [ ! -f "$state_dir/previous-tag" ]; then
    echo "Aucune version précédente n'est enregistrée." >&2
    exit 1
fi

previous_tag=$(sed -n '1p' "$state_dir/previous-tag")
current_tag=$(sed -n '1p' "$state_dir/current-tag")
export IMAGE_TAG="$previous_tag"

compose() {
    docker compose --env-file "$env_file" -f "$project_dir/docker-compose.prod.yaml" "$@"
}

compose pull backend nginx frontend
compose up -d --wait --wait-timeout 180 backend nginx frontend edge

printf '%s\n' "$current_tag" > "$state_dir/previous-tag"
printf '%s\n' "$previous_tag" > "$state_dir/current-tag"
echo "Retour applicatif vers $previous_tag terminé. Les migrations de base ne sont pas annulées."
