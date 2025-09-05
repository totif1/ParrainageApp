#!/bin/bash
# backend/docker-entrypoint-dev.sh

set -e

echo "🚀 Démarrage en mode développement..."

# Si pas de vendor, installer les dépendances
if [ ! -d "vendor" ]; then
    echo "📦 Installation des dépendances Composer..."
    composer install --no-scripts --no-autoloader
    composer dump-autoload
fi

# Permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "✅ Prêt pour le développement!"
echo "📝 Modifications de code détectées automatiquement"

# Démarrer Apache
exec apache2-foreground