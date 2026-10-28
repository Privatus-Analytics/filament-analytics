#!/bin/sh
# Runs composer validate, the tests, PHP-CS-Fixer and PHPStan in Docker.
# Usage: ./run-docker.sh            (PHP 8.3, newest Filament)
#        PHP=8.2 FILAMENT=4 ./run-docker.sh
set -e
PHP="${PHP:-8.3}"
docker build -q --build-arg PHP="$PHP" -t "privatus-filament-test:$PHP" . >/dev/null
docker run --rm -e FILAMENT="${FILAMENT:-}" -v "$PWD":/app -w /app "privatus-filament-test:$PHP" sh -c '
  set -e
  rm -f composer.lock
  composer validate --strict
  cp composer.json /tmp/composer.json.orig
  trap "cp /tmp/composer.json.orig /app/composer.json" EXIT
  if [ -n "$FILAMENT" ]; then composer require --no-update "filament/filament:^$FILAMENT.0"; fi
  composer update --no-interaction --no-progress --prefer-dist --quiet
  composer show filament/filament | grep -E "^versions"
  vendor/bin/phpunit
  vendor/bin/php-cs-fixer fix --dry-run --diff --show-progress=none
  vendor/bin/phpstan analyse --memory-limit=1G --no-progress
'
