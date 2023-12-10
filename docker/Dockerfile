FROM php:8.2.12-cli-alpine3.18

WORKDIR /var/www/app/

RUN apk add  git

RUN apk update && apk upgrade && apk  add autoconf build-base
RUN apk add  linux-headers
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer
