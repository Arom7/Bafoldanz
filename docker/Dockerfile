# BAFOLDANZ Digital — imagen multi-stage (local + producción).
# Local usa el stage "dev"; producción el stage "prod" (se completa en Fase 5).
# Todo corre como uid/gid 1000 (usuario `app`) para que los archivos generados
# dentro del contenedor queden con el dueño del host. Ver docs/05-despliegue.md.

ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm-alpine AS base

ARG UID=1000
ARG GID=1000

# Extensiones requeridas por Laravel y por el stack del proyecto:
# - pdo_mysql: Eloquent sobre MySQL 8.0
# - gd: barryvdh/laravel-dompdf (v1.1)
# - zip, intl, bcmath, pcntl: Laravel / dependencias
# - opcache: rendimiento (se configura en php.ini de producción)
RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        zip \
        gd \
        bcmath \
        pcntl \
        opcache

# Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Usuario de servicio con uid/gid del host (Alpine: addgroup/adduser, shell sh)
RUN addgroup -g "${GID}" app \
    && adduser -D -u "${UID}" -G app -s /bin/sh app

WORKDIR /var/www

FROM base AS dev

ARG UID=1000
ARG GID=1000

# ini de desarrollo (errores visibles; xdebug activable por variable de entorno)
COPY docker/local.ini /usr/local/etc/php/conf.d/app.ini

# Xdebug opcional (build-arg XDEBUG=true)
ARG XDEBUG=false
RUN if [ "${XDEBUG}" = "true" ]; then \
        pecl install xdebug \
        && docker-php-ext-enable xdebug \
    ; fi

USER app

# En dev el código entra por bind mount; el entrypoint ejecuta `artisan serve`.
CMD ["docker/entrypoint.sh"]

FROM base AS prod

ARG UID=1000
ARG GID=1000

# ini de producción (opcache on, memory_limit, realpath_cache)
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Entrypoint de producción: espera MySQL → migrate --force + caches → php-fpm
COPY --chmod=0755 docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# El código de producción se copia dentro de la imagen (Fase 5 completa el
# build de Vite con stage node intermedio y el compose de producción).
COPY --chown=${UID}:${GID} . /var/www

RUN composer install --no-dev --optimize-autoloader --no-interaction

USER app

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
