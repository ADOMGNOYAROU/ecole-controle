# Image de production d'École Manager (Laravel) pour Render.
# Étape 1 : compilation de l'interface (Vite/Tailwind).
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# Étape 2 : application PHP servie par Apache.
FROM php:8.3-apache

# Extensions PHP nécessaires : PostgreSQL, PDF (gd), Excel/zip, calculs, cache.
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql gd zip bcmath intl opcache exif

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1

# Apache : racine sur public/, réécriture d'URL pour les routes Laravel.
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit=256M" > "$PHP_INI_DIR/conf.d/ecole.ini" \
    && echo "upload_max_filesize=10M" >> "$PHP_INI_DIR/conf.d/ecole.ini" \
    && echo "post_max_size=12M" >> "$PHP_INI_DIR/conf.d/ecole.ini"

WORKDIR /var/www/html

# Dépendances PHP d'abord (couche mise en cache tant que composer.lock ne change pas).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 10000
CMD ["/usr/local/bin/entrypoint.sh"]
