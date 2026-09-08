#!/bin/sh
set -e

cd /var/www/html

# 1. Dépendances PHP (le code est monté en volume en dev)
if [ ! -d vendor ] || [ ! -f vendor/autoload_runtime.php ]; then
    echo "→ composer install"
    composer install --no-interaction --prefer-dist --no-progress
fi

# 2. Attendre MySQL
if [ -n "$DATABASE_URL" ]; then
    echo "→ attente de la base de données"
    tries=0
    until php -r '
        $u = getenv("DATABASE_URL");
        $p = parse_url($u);
        try {
            new PDO(sprintf("mysql:host=%s;port=%d", $p["host"], $p["port"] ?? 3306), $p["user"], $p["pass"] ?? "");
            exit(0);
        } catch (Throwable $e) { exit(1); }
    ' 2>/dev/null; do
        tries=$((tries + 1))
        [ "$tries" -ge 30 ] && echo "  base de données injoignable, on continue quand même" && break
        sleep 2
    done
fi

# 3. Migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

# 4. CSS Tailwind (compilé une fois ; utiliser "tailwind:build --watch" pour le dev)
php bin/console tailwind:build --minify || true

# 5. Permissions cache/log
mkdir -p var/cache var/log
chown -R www-data:www-data var

exec "$@"
