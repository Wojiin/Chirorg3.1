#!/bin/sh
set -eu

php bin/console cache:clear --no-warmup
php bin/console cache:warmup

exec "$@"
