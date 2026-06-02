FROM php:8.2-apache

LABEL maintainer="CL Dev"

RUN a2enmod rewrite headers

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    xml \
    bcmath \
    curl \
    gd \
    zip \
    intl \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY .docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN composer install --no-interaction

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN chown -R www-data:www-data storage bootstrap/cache public && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]

