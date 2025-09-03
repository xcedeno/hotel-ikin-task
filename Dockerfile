FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip

RUN docker-php-ext-install pdo_mysql mbstring gd

RUN a2enmod rewrite
WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini