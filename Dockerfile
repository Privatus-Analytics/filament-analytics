# Test image: official PHP CLI with intl (Filament needs it) and Composer from the official image.
ARG PHP=8.3
FROM composer:2.10.2 AS composer
FROM php:${PHP}-cli-bookworm
RUN apt-get update -qq && apt-get install -y -qq --no-install-recommends git unzip libicu-dev libzip-dev >/dev/null \
    && docker-php-ext-install -j"$(nproc)" intl zip >/dev/null \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app
