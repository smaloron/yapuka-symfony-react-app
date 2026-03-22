# =============================================================================
# Dockerfile - Backend Symfony (PHP-FPM)
# =============================================================================
# Image multi-stage :
#   1. Installation des dépendances Composer
#   2. Image PHP-FPM avec les extensions nécessaires
#
# Extensions PHP installées :
#   - pdo_pgsql : driver PostgreSQL
#   - intl      : internationalisation
#   - opcache   : cache du bytecode PHP (performances)
#   - redis     : extension Redis pour le cache
#   - xdebug    : débogage et couverture de code (dev uniquement)
# =============================================================================

FROM php:8.4-fpm-alpine3.22

RUN apk update && apk upgrade --no-cache

RUN apk update && apk upgrade --no-cache

# --- Installer les dépendances système ---
RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# --- Installer les extensions PHP ---
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    opcache

# --- Installer l'extension Redis via PECL ---
RUN pecl install redis && docker-php-ext-enable redis

# --- Installer Xdebug (pour le développement et la couverture de code) ---
RUN pecl install xdebug && docker-php-ext-enable xdebug

# --- Configuration Xdebug ---
RUN echo "xdebug.mode=develop,debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# --- Installer Composer (gestionnaire de paquets PHP) ---
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Répertoire de travail ---
WORKDIR /var/www/api

# --- Copier les fichiers du projet ---
COPY . .

# --- Installer les dépendances PHP ---
RUN composer install --no-interaction --optimize-autoloader

# --- Permissions pour le cache Symfony ---
RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var/

# --- Exposer le port PHP-FPM ---
EXPOSE 9000

CMD ["php-fpm"]
