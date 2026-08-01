#!/usr/bin/env bash
# Production deploy script for invoiceApp.
# Drives the existing compose.yaml / Sail Dockerfile as-is:
# pulls latest code, builds/starts containers, installs deps,
# runs migrations, and caches config. Run from the server, in the repo root.
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE="docker compose"
SERVICES="app queue mysql redis" # mysql_test is dev-only, skipped in production

echo "==> Pulling latest code"
git fetch origin
git pull --ff-only origin "$(git rev-parse --abbrev-ref HEAD)"

if [ ! -f vendor/laravel/sail/runtimes/8.5/Dockerfile ]; then
  echo "==> vendor/ missing (first run) — bootstrapping via a throwaway composer container"
  docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/app" \
    composer:2 \
    install --ignore-platform-reqs --no-dev --optimize-autoloader --no-interaction
fi

if [ ! -f .env ]; then
  echo "==> No .env found — creating from .env.example"
  cp .env.example .env
  echo "!! Edit .env now with production values (DB_PASSWORD, MAIL_*, APP_URL, APP_PORT, etc.), then re-run this script."
  exit 1
fi

APP_PORT_VAL="$(grep -E '^APP_PORT=' .env | cut -d= -f2 || true)"
if [ -z "$APP_PORT_VAL" ] || [ "$APP_PORT_VAL" = "80" ]; then
  echo "!! Warning: APP_PORT is unset or 80 in .env — that will conflict with host Caddy on port 80."
  echo "!! Set APP_PORT=8080 in .env and point Caddy's reverse_proxy at 127.0.0.1:8080."
fi

NEED_KEY=0
if ! grep -q '^APP_KEY=base64:' .env; then
  NEED_KEY=1
fi

echo "==> Building app image"
$COMPOSE build app

echo "==> Starting containers"
$COMPOSE up -d --remove-orphans $SERVICES

echo "==> Waiting for MySQL to be healthy"
until [ "$($COMPOSE ps -q mysql | xargs docker inspect -f '{{.State.Health.Status}}')" = "healthy" ]; do
  sleep 2
done

if [ "$NEED_KEY" = "1" ]; then
  echo "==> No APP_KEY set — generating one"
  $COMPOSE exec -T app php artisan key:generate --force
fi

echo "==> Installing PHP dependencies"
$COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing & building frontend assets"
$COMPOSE exec -T app npm ci
$COMPOSE exec -T app npm run build

echo "==> Running database migrations"
$COMPOSE exec -T app php artisan migrate --force

echo "==> Linking storage"
$COMPOSE exec -T app bash -c '[ -L public/storage ] || php artisan storage:link'

echo "==> Caching config/routes/views"
$COMPOSE exec -T app php artisan config:clear
$COMPOSE exec -T app php artisan optimize

echo "==> Restarting queue worker to pick up new code"
$COMPOSE restart queue

echo "==> Deploy complete."
