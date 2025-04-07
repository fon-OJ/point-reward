FROM php:8.2-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    unzip curl libzip-dev zip \
    && docker-php-ext-install zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
