# syntax=docker/dockerfile:1
# ---------------------------------------------------------------------------
#  Image applicative : Symfony 7.4 servi par Apache + PHP 8.4
#  Multi-stage :
#    - base : tout ce qui est commun (PHP, extensions, Apache, Composer)
#    - dev  : le code est monté en volume, deps installées au démarrage
#    - prod : le code + les dépendances + les assets sont cuits dans l'image
#  Choisir la cible avec `docker build --target dev|prod .`
# ---------------------------------------------------------------------------

FROM php:8.4-apache AS base

# --- Dépendances système + extensions PHP --------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" intl pdo_mysql zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --- Composer ----------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Apache : docroot sur public/, réécriture activée ---------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    AllowOverride All\n    Require all granted\n    FallbackResource /index.php\n</Directory>\n' \
        > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# --- PHP config ------------------------------------------------------------
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'memory_limit=256M'; \
        echo 'realpath_cache_size=4096K'; \
        echo 'realpath_cache_ttl=600'; \
    } > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html


# ===========================================================================
#  DEV — utilisé par compose.yaml (bind-mount + install au runtime)
# ===========================================================================
FROM base AS dev

COPY docker/app-entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]


# ===========================================================================
#  PROD — image autoportante publiée sur le registre (ghcr.io)
# ===========================================================================
FROM base AS prod

ENV APP_ENV=prod \
    APP_DEBUG=0

# 1) Dépendances d'abord (cache Docker tant que composer.lock ne bouge pas)
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-progress --prefer-dist --no-interaction

# 2) Le code
COPY . .

# 3) Autoload optimisé + scripts Symfony + assets + cache.
#    APP_SECRET / DATABASE_URL ne servent qu'à faire passer le warmup : les
#    vraies valeurs sont injectées au runtime (docker run -e / compose).
RUN APP_SECRET=build DATABASE_URL="mysql://u:p@127.0.0.1:3306/app?serverVersion=8.0.37" \
    sh -c 'composer dump-autoload --no-dev --optimize --classmap-authoritative \
        && composer run-script post-install-cmd --no-interaction \
        && php bin/console tailwind:build --minify \
        && php bin/console cache:warmup \
        && chown -R www-data:www-data var'

COPY docker/prod-entrypoint.sh /usr/local/bin/prod-entrypoint
RUN chmod +x /usr/local/bin/prod-entrypoint

ENTRYPOINT ["prod-entrypoint"]
CMD ["apache2-foreground"]
