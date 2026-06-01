FROM php:8.4-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    git curl libpng-dev oniguruma-dev libxml2-dev \
    zip unzip linux-headers postgresql-dev $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-install \
    pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Bootstrap a fresh Laravel 10 project, then install guzzle for HTTP service calls
RUN composer create-project laravel/laravel:^10.0 . --prefer-dist --no-interaction --no-scripts \
    && composer require guzzlehttp/guzzle --no-interaction

# Overlay service-specific application files
COPY app/ app/
COPY database/ database/
COPY routes/ routes/
COPY .env.example .env.example

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
