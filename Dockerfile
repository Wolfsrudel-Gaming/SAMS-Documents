FROM php:8.3-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg62-turbo-dev libwebp-dev libzip-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd opcache \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/sams.ini

WORKDIR /var/www/sams
