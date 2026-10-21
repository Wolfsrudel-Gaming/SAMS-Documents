# Feste, gepatchte PHP-Version (PHP2Shell: CVE-2026-109618/-109619 ab 8.3.36 behoben).
# Eine neue Version hier eintragen – das Auto-Update baut den Container dann neu.
FROM php:8.3.36-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg62-turbo-dev libwebp-dev libzip-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd opcache \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/sams.ini

WORKDIR /var/www/sams
