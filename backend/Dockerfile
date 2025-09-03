FROM php:8.2-apache

# Installation des dépendances
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

# Activation des modules Apache nécessaires
RUN a2enmod rewrite headers

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie des fichiers de dépendances
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copie du code source
COPY . .

# Configuration des permissions
RUN mkdir -p logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 logs

# Copie de la configuration Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf