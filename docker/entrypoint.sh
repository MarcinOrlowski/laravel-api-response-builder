#!/usr/bin/env bash
# Ensure dependencies exist in the container's vendor volume, then run the
# requested command (defaults to the test suite via the compose `command:`).
set -euo pipefail

if [[ ! -f vendor/autoload.php ]]; then
    echo ">> vendor/ empty — running composer install (PHP $(php -r 'echo PHP_VERSION;'))"
    composer install --no-interaction --no-progress
fi

exec "$@"
