# Usar imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo "zend_extension=xdebug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Cambiar el directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de configuración
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Exponer puerto
EXPOSE 80