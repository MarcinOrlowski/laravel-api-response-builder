# PHP 8.3 test image for laravel-api-response-builder.
# Matches the lowest supported PHP version (composer.json: ^8.3) so the local
# suite catches anything that only breaks on 8.3.
FROM php:8.3-cli

# mlocati installer resolves the right system deps per extension.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
        mbstring \
        intl \
        bcmath \
        zip \
        gd \
        pcntl \
        sockets \
        pcov

# Composer (copied from the official image).
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# pcov is present but off by default; tests without --coverage stay fast.
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

WORKDIR /app
