FROM php:8.2.12-cli-alpine3.18

WORKDIR /var/www/app/

RUN apk add  git



COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer
