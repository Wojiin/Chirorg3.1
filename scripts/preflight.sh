#!/bin/sh
set -eu

project_dir=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
env_file=${ENV_FILE:-"$project_dir/.env.prod"}

fail() {
    echo "Préflight refusé : $1" >&2
    exit 1
}

env_value() {
    sed -n "s/^$1=//p" "$env_file" | tail -n 1 | tr -d '\r'
}

[ -f "$env_file" ] || fail "fichier $env_file introuvable."
command -v docker >/dev/null 2>&1 || fail "Docker est introuvable."
command -v openssl >/dev/null 2>&1 || fail "OpenSSL est introuvable."

for name in APP_DOMAIN APP_HOST ACME_EMAIL MYSQL_DATABASE MYSQL_USER MYSQL_PASSWORD MYSQL_ROOT_PASSWORD APP_SECRET DATABASE_URL DEFAULT_URI CORS_ALLOW_ORIGIN TRUSTED_HOSTS JWT_PASSPHRASE JWT_PRIVATE_KEY_PATH JWT_PUBLIC_KEY_PATH; do
    value=$(env_value "$name")
    [ -n "$value" ] || fail "$name est absent."
    if printf '%s' "$value" | grep -Eqi 'a-remplacer|example\.org|change-?me'; then
        fail "$name contient encore une valeur d'exemple."
    fi
done

[ "$(printf '%s' "$(env_value APP_SECRET)" | wc -c)" -ge 32 ] || fail "APP_SECRET doit contenir au moins 32 caractères."
[ "$(printf '%s' "$(env_value JWT_PASSPHRASE)" | wc -c)" -ge 16 ] || fail "JWT_PASSPHRASE doit contenir au moins 16 caractères."
printf '%s' "$(env_value CORS_ALLOW_ORIGIN)" | grep -Fq '.*' && fail "CORS_ALLOW_ORIGIN est trop permissif."
printf '%s' "$(env_value TRUSTED_HOSTS)" | grep -Fq '.*' && fail "TRUSTED_HOSTS est trop permissif."

app_domain=$(env_value APP_DOMAIN)
app_host=$(env_value APP_HOST)
[ "$app_domain" = "$app_host" ] || fail "APP_DOMAIN et APP_HOST doivent être identiques."
[ "$(env_value DEFAULT_URI)" = "https://$app_domain" ] || fail "DEFAULT_URI doit utiliser HTTPS et APP_DOMAIN."

resolve_path() {
    case "$1" in
        /*) printf '%s\n' "$1" ;;
        *) printf '%s\n' "$project_dir/${1#./}" ;;
    esac
}

private_key=$(resolve_path "$(env_value JWT_PRIVATE_KEY_PATH)")
public_key=$(resolve_path "$(env_value JWT_PUBLIC_KEY_PATH)")
[ -r "$private_key" ] || fail "clé JWT privée illisible."
[ -r "$public_key" ] || fail "clé JWT publique illisible."

if command -v stat >/dev/null 2>&1; then
    env_mode=$(stat -c '%a' "$env_file")
    private_mode=$(stat -c '%a' "$private_key")
    [ "$env_mode" -le 640 ] || fail "$env_file doit être protégé avec chmod 640 au maximum."
    [ "$private_mode" -le 600 ] || fail "la clé JWT privée doit être protégée avec chmod 600 au maximum."
fi

temporary_public=$(mktemp)
trap 'rm -f "$temporary_public"' EXIT INT TERM
JWT_AUDIT_PASSPHRASE=$(env_value JWT_PASSPHRASE)
export JWT_AUDIT_PASSPHRASE
openssl pkey -in "$private_key" -passin env:JWT_AUDIT_PASSPHRASE -pubout -out "$temporary_public" >/dev/null 2>&1 \
    || fail "la clé JWT privée ou sa phrase secrète est invalide."
openssl pkey -pubin -in "$public_key" -noout >/dev/null 2>&1 || fail "la clé JWT publique est invalide."
cmp -s "$temporary_public" "$public_key" || fail "les clés JWT privée et publique ne correspondent pas."
unset JWT_AUDIT_PASSPHRASE

available_kb=$(df -Pk "$project_dir" | awk 'NR == 2 {print $4}')
[ "${available_kb:-0}" -ge 2097152 ] || fail "moins de 2 Go sont disponibles sur le disque."

docker compose --env-file "$env_file" -f "$project_dir/docker-compose.prod.yaml" config --quiet
echo "Préflight de production validé."
