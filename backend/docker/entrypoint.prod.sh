#!/bin/sh
set -eu

runtime_secrets=/run/chirorg
install -d -m 700 -o www-data -g www-data "$runtime_secrets"
install -m 600 -o www-data -g www-data /run/secrets/jwt_private.pem "$runtime_secrets/jwt_private.pem"
install -m 644 -o www-data -g www-data /run/secrets/jwt_public.pem "$runtime_secrets/jwt_public.pem"

su-exec www-data php bin/console cache:clear --no-warmup
su-exec www-data php bin/console cache:warmup

if [ "${1:-}" = "php-fpm" ]; then
    exec "$@"
fi

exec su-exec www-data "$@"
