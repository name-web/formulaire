<?php

/*
 * Applies the machine-specific database credentials from .env.testing
 * (git-ignored) to the current process BEFORE the Laravel application boots.
 *
 * Writing to $_SERVER, $_ENV and putenv() is required because Laravel's Env
 * repository reads $_SERVER first: variables defined at the OS level would
 * otherwise shadow the values coming from .env.testing.
 *
 * Only connection details are applied here. Everything else in .env.testing
 * behaves like a regular Laravel environment file, and the safety pins
 * (APP_ENV, DB_CONNECTION, DB_DATABASE, ...) always win from phpunit.xml.
 */

$overrides = ['DB_HOST', 'DB_PORT', 'DB_USERNAME', 'DB_PASSWORD'];

$envFile = dirname(__DIR__).'/.env.testing';

if (! is_file($envFile)) {
    return;
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if ($lines === false) {
    return;
}

foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    $separator = strpos($line, '=');

    if ($separator === false) {
        continue;
    }

    $key = trim(substr($line, 0, $separator));

    if (! in_array($key, $overrides, true)) {
        continue;
    }

    $value = trim(substr($line, $separator + 1));

    if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && str_ends_with($value, $value[0])) {
        $value = substr($value, 1, -1);
    }

    $_SERVER[$key] = $value;
    $_ENV[$key] = $value;
    putenv("{$key}={$value}");
}
