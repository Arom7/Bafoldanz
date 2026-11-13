#!/bin/sh
# Entrypoint según docs/05-despliegue.md.
# - Desarrollo: exige .env (lo crea `make setup`) y ejecuta artisan serve.
# - Producción: espera MySQL, migrate --force + caches (Fase 5).

set -e

cd /var/www

if [ "${APP_ENV}" = "production" ]; then
    # 1. Esperar a MySQL (loop con mysqladmin ping no está en la imagen: probe con PHP)
    until php -r 'try { new PDO("mysql:host=" . getenv("DB_HOST") . ";port=" . (getenv("DB_PORT") ?: "3306"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); echo "ok"; } catch (Exception $e) { exit(1); }' >/dev/null 2>&1; do
        echo "Esperando MySQL..."
        sleep 2
    done

    # 2. Migraciones (nunca migrate:fresh, regla del proyecto)
    php artisan migrate --force

    # 3. Caches
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # 4. Dueño de storage/ y bootstrap/cache idempotente hacia 1000:1000
    if [ "$(id -u)" = "0" ]; then
        [ -d storage ] && find storage -not -uid 1000 -exec chown 1000:1000 {} + || true
        [ -d bootstrap/cache ] && find bootstrap/cache -not -uid 1000 -exec chown 1000:1000 {} + || true
    fi

    # 5. PHP-FPM como proceso principal
    exec "$@"
fi

# --- Desarrollo ---
if [ ! -f .env ]; then
    echo "ERROR: no existe .env. Créalo con 'make setup' antes de levantar el stack." >&2
    exit 1
fi

exec php artisan serve --host=0.0.0.0 --port=8000
