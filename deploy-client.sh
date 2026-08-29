#!/usr/bin/env bash
# Provision a brand-new, independent instance of this stack on the SAME host
# (separate containers, DB, ports, and domain) for a new client.
#
# Usage: ./deploy-client.sh <client-slug> <domain> [base-app-port] [base-db-port]
#   client-slug      lowercase, alphanumeric/hyphen, e.g. clientb
#   domain           e.g. clientb-domain.com (used for APP_URL)
#   base-app-port    first port tried for the web UI (default 8000)
#   base-db-port     first port tried for the DB (default 3307)
set -euo pipefail

if [ $# -lt 2 ]; then
    echo "Usage: $0 <client-slug> <domain> [base-app-port] [base-db-port]" >&2
    exit 1
fi

CLIENT="$1"
DOMAIN="$2"
BASE_APP_PORT="${3:-8000}"
BASE_DB_PORT="${4:-3307}"

if ! [[ "$CLIENT" =~ ^[a-z0-9][a-z0-9-]*$ ]]; then
    echo "Client slug must be lowercase alphanumeric/hyphen (e.g. clientb)" >&2
    exit 1
fi

SOURCE_DIR="$(cd "$(dirname "$0")" && pwd)"
PARENT_DIR="$(dirname "$SOURCE_DIR")"
TARGET_DIR="$PARENT_DIR/inallcart-$CLIENT"

if [ -d "$TARGET_DIR" ]; then
    echo "Target directory already exists: $TARGET_DIR" >&2
    exit 1
fi

# a port is free if nothing is currently listening on it
is_port_free() {
    ! ss -ltn "( sport = :$1 )" 2>/dev/null | grep -q ":$1"
}

find_free_port() {
    local port=$1
    while ! is_port_free "$port"; do
        port=$((port + 1))
    done
    echo "$port"
}

APP_PORT=$(find_free_port "$BASE_APP_PORT")
DB_PORT=$(find_free_port "$BASE_DB_PORT")

echo "==> Client:           $CLIENT"
echo "==> Domain:           $DOMAIN"
echo "==> Target dir:       $TARGET_DIR"
echo "==> APP_PORT:         $APP_PORT"
echo "==> DB_FORWARD_PORT:  $DB_PORT"

if git -C "$SOURCE_DIR" remote get-url origin >/dev/null 2>&1; then
    ORIGIN_URL="$(git -C "$SOURCE_DIR" remote get-url origin)"
    echo "==> Cloning from $ORIGIN_URL"
    git clone "$ORIGIN_URL" "$TARGET_DIR"
else
    echo "==> No git remote configured, copying the working tree instead"
    tar -C "$SOURCE_DIR" -cf - \
        --exclude='.git' --exclude='vendor' --exclude='node_modules' \
        --exclude='.env' --exclude='storage/logs' \
        --exclude='storage/framework/cache' --exclude='storage/framework/sessions' \
        --exclude='storage/framework/views' . \
        | (mkdir -p "$TARGET_DIR" && tar -C "$TARGET_DIR" -xf -)
fi

cd "$TARGET_DIR"

DB_PASSWORD="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9')"
# generated up front: docker-compose's env_file bakes .env into each
# container's environment at creation time, so a key written later by
# `artisan key:generate` at runtime would be shadowed by the empty value
APP_KEY="base64:$(openssl rand -base64 32)"

cp .env.example .env
sed -i \
    -e "s|^APP_NAME=.*|APP_NAME=\"${CLIENT}\"|" \
    -e "s|^APP_ENV=.*|APP_ENV=production|" \
    -e "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" \
    -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
    -e "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" \
    -e "s|^DB_DATABASE=.*|DB_DATABASE=${CLIENT}_db|" \
    -e "s|^DB_USERNAME=.*|DB_USERNAME=${CLIENT}|" \
    -e "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" \
    -e "s|^IMAGE_NAME=.*|IMAGE_NAME=inallcart-${CLIENT}-app|" \
    -e "s|^APP_PORT=.*|APP_PORT=${APP_PORT}|" \
    -e "s|^DB_FORWARD_PORT=.*|DB_FORWARD_PORT=${DB_PORT}|" \
    .env
echo "COMPOSE_PROJECT_NAME=${CLIENT}" >> .env

# the app image runs as uid 1000 ("www"); bind-mounted host paths must match
chown -R 1000:1000 storage bootstrap/cache
chown 1000:1000 .env
chmod 664 .env

echo "==> Building images"
docker compose build

echo "==> Starting containers"
docker compose up -d

echo "==> Waiting for the database to accept connections"
for i in $(seq 1 40); do
    if docker compose exec -T db mysqladmin ping -h localhost -u"${CLIENT}" -p"${DB_PASSWORD}" --silent >/dev/null 2>&1; then
        break
    fi
    sleep 3
done

echo "==> Running migrations"
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan storage:link || true

cat <<EOF

==> Instance ready
    Client:       $CLIENT
    Directory:    $TARGET_DIR
    Domain:       $DOMAIN
    App port:     $APP_PORT   (proxy this from your host-level reverse proxy)
    DB port:      $DB_PORT
    DB password:  $DB_PASSWORD   (also saved in $TARGET_DIR/.env)

Next steps:
  1. Point DNS for $DOMAIN at this server.
  2. Add a host-level reverse proxy entry: $DOMAIN -> 127.0.0.1:$APP_PORT
  3. Issue a TLS certificate for $DOMAIN (e.g. certbot).
  4. Future updates to this instance: cd "$TARGET_DIR" && ./deploy.sh
EOF
