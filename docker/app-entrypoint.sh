#!/bin/sh
set -e

cd /var/www/html

# 1. Dépendances PHP (le code est monté en volume en dev).
#    On lance toujours "install" : c'est quasi instantané si tout est à jour,
#    et ça évite d'oublier une dépendance ajoutée depuis l'hôte.
echo "→ composer install"
composer install --no-interaction --prefer-dist --no-progress

# 2. Attendre que MySQL accepte les connexions
if [ -n "$DATABASE_URL" ]; then
    echo "→ attente de la base de données"
    tries=0
    until php -r '
        $p = parse_url(getenv("DATABASE_URL"));
        try {
            new PDO(
                sprintf("mysql:host=%s;port=%d;dbname=%s", $p["host"], $p["port"] ?? 3306, ltrim($p["path"] ?? "", "/")),
                $p["user"] ?? "root",
                $p["pass"] ?? ""
            );
            exit(0);
        } catch (Throwable $e) { exit(1); }
    ' 2>/dev/null; do
        tries=$((tries + 1))
        if [ "$tries" -ge 30 ]; then
            echo "  ✗ base de données injoignable après 60 s" >&2
            exit 1
        fi
        sleep 2
    done
fi

# 3. Migrations (schéma + données de démo) — on échoue bruyamment si ça casse
echo "→ migrations"
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# 4. CSS Tailwind (build unique ; en dev, préférer "tailwind:build --watch")
echo "→ build CSS"
php bin/console tailwind:build --minify

# 5. Permissions cache / log
mkdir -p var/cache var/log
chown -R www-data:www-data var

exec "$@"
