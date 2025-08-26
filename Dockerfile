FROM php:8.2-fpm-alpine

RUN set -ex \
    && apk add --no-cache \
    bash git curl icu-dev libzip-dev oniguruma-dev sqlite-libs sqlite-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql pdo_sqlite mbstring bcmath intl zip opcache \
    && rm -rf /var/cache/apk/*

RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

CMD ["php-fpm"]
