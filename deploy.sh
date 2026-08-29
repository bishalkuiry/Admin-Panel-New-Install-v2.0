#!/usr/bin/env bash
# Production deploy script for InAllCart.
# Usage: ./deploy.sh [--migrate] [--no-pull]
#   --migrate   run `php artisan migrate --force` after the new image is up
#   --no-pull   skip `git pull` (use the code already checked out)
set -euo pipefail
cd "$(dirname "$0")"

DO_MIGRATE=false
DO_PULL=true
for arg in "$@"; do
    case "$arg" in
        --migrate) DO_MIGRATE=true ;;
        --no-pull) DO_PULL=false ;;
        *) echo "Unknown option: $arg" >&2; exit 1 ;;
    esac
done

if [ "$DO_PULL" = true ]; then
    echo "==> Pulling latest code"
    git pull
fi

echo "==> Building app image (app, queue, scheduler)"
docker compose build app queue scheduler

echo "==> Recreating app containers"
docker compose up -d app queue scheduler

if [ "$DO_MIGRATE" = true ]; then
    echo "==> Running database migrations"
    docker compose exec app php artisan migrate --force
fi

echo "==> Restarting webserver (picks up the app container's new address)"
docker compose restart webserver

echo "==> Waiting for the app to respond"
for i in $(seq 1 15); do
    if docker compose exec -T app php artisan --version >/dev/null 2>&1; then
        break
    fi
    sleep 1
done

echo "==> Recent app logs"
docker compose logs app --tail=20

echo "==> Done"
