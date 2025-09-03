# Utiliser l'image PHP officielle avec Apache
FROM php:8.2-apache

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite pour Apache
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de configuration Composer
COPY composer.json composer.lock ./

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copier tout le code source
COPY . .

# Créer les dossiers nécessaires avec les bonnes permissions
RUN mkdir -p logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 logs

# Configuration Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Exposer le port 80
EXPOSE 80

# Commande par défaut
CMD ["apache2-foreground"]