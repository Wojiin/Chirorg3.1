#!/bin/sh
set -eu

cleanup_hour=${PROGRAMME_CLEANUP_HOUR:-6}

run_cleanup() {
    if ! "$@"; then
        echo "Échec de la tâche de maintenance : $*" >&2
    fi
}

while true; do
    wait_seconds=$(PROGRAMME_CLEANUP_HOUR="$cleanup_hour" php -r '
        $timezone = new DateTimeZone("Europe/Paris");
        $now = new DateTimeImmutable("now", $timezone);
        $hour = filter_var(getenv("PROGRAMME_CLEANUP_HOUR"), FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 23]]);
        if (false === $hour) {
            $hour = 6;
        }
        $nextRun = $now->setTime($hour, 0);
        if ($nextRun <= $now) {
            $nextRun = $nextRun->modify("+1 day");
        }
        echo $nextRun->getTimestamp() - $now->getTimestamp();
    ')

    sleep "$wait_seconds"
    run_cleanup php bin/console app:programmes:purge-expired --no-interaction
    run_cleanup php bin/console app:fiches-techniques:purge-orphan-images --older-than=86400 --no-interaction
    run_cleanup php bin/console gesdinet:jwt:clear --no-interaction
done
