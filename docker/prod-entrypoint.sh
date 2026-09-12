#!/bin/sh
# Entrypoint de l'image de production :
#   attend la base, applique les migrations, puis lance Apache.
set -e

cd /var/www/html

if [ -n "$DATABASE_URL" ]; then
    echo "→ attente de la base de données"
    tries=0
    until php -r '
        $p = parse_url(getenv("DATABASE_URL"));
        try {
            new PDO(
                sprintf("mysql:host=%s;port=%d", $p["host"], $p["port"] ?? 3306),
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

echo "→ migrations"
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Fix Railway : force mpm_prefork (seul MPM compatible avec mod_php)
a2dismod mpm_event mpm_worker 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Fix Railway : fait écouter Apache sur le port fourni par la plateforme
sed -ri "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT:-8080}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
