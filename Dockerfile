FROM php:8.2-apache

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        gd \
        curl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize = 512M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copier uniquement composer.json pour la mise en cache des couches
COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader

# Copier le reste du code
COPY . /var/www/html/

# Générer l'autoloader après avoir copié tout le code
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/public/uploads

EXPOSE 80

CMD ["apache2-foreground"]
