#!/bin/sh
set -e

# ── Wait for MySQL ────────────────────────────────────────────────────────────
echo "[entrypoint] Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306} ..."
until php -r '
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $db   = getenv("DB_DATABASE");
    $user = getenv("DB_USERNAME");
    $pass = getenv("DB_PASSWORD");
    try {
        new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
        exit(0);
    } catch (Exception $e) {
        exit(1);
    }
' 2>/dev/null; do
    printf '.'
    sleep 2
done
echo ""
echo "[entrypoint] MySQL ready."

# ── Generate APP_KEY if not provided ─────────────────────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "[entrypoint] Generating APP_KEY..."
    php artisan key:generate --force
fi

# ── Discover and cache service providers ─────────────────────────────────────
php artisan package:discover --ansi

# ── Run pending migrations ────────────────────────────────────────────────────
echo "[entrypoint] Running migrations..."
php artisan migrate --force

# ── Create storage symlink ───────────────────────────────────────────────────
php artisan storage:link --force 2>/dev/null || true

# ── Production caches ────────────────────────────────────────────────────────
if [ "$APP_ENV" = "production" ]; then
    echo "[entrypoint] Caching config, routes, and views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "[entrypoint] Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
