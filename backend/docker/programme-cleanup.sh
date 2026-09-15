#!/bin/sh
set -eu

cleanup_hour=${PROGRAMME_CLEANUP_HOUR:-6}

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
    php bin/console app:programmes:purge-expired --no-interaction
done
