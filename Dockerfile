FROM php:8.2-apache

RUN a2enmod rewrite headers && a2dismod reqtimeout

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    default-mysql-client \
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

COPY .docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/php/php.ini /usr/local/etc/php/conf.d/datacheck.ini
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
