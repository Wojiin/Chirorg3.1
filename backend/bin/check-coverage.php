<?php

declare(strict_types=1);

if (3 !== $argc) {
    fwrite(STDERR, "Usage: php bin/check-coverage.php <clover.xml> <minimum-percent>\n");
    exit(2);
}

$report = $argv[1];
$minimum = filter_var($argv[2], FILTER_VALIDATE_FLOAT);
if (!is_file($report) || false === $minimum) {
    fwrite(STDERR, "Coverage report or minimum threshold is invalid.\n");
    exit(2);
}

$xml = simplexml_load_file($report);
$metrics = $xml?->project?->metrics;
$statements = (int) ($metrics['statements'] ?? 0);
$covered = (int) ($metrics['coveredstatements'] ?? 0);
if (0 === $statements) {
    fwrite(STDERR, "The coverage report contains no executable statements.\n");
    exit(2);
}

$coverage = 100 * $covered / $statements;
printf("Backend line coverage: %.2f%% (minimum %.2f%%)\n", $coverage, $minimum);
exit($coverage + 0.00001 >= $minimum ? 0 : 1);
