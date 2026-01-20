FROM php:8.4-fpm-alpine

# Установка зависимостей
RUN apk update && apk add \
    curl \
    git \
    build-base \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    libpq-dev \
    oniguruma-dev \
    libxml2-dev \
    nodejs \
    npm

# Установка расширений PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка PHP
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Создание пользователя для приложения
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

# Установка рабочей директории
WORKDIR /var/www/html

# Копирование файлов приложения
COPY --chown=laravel:laravel . .

# Установка прав
RUN chown -R laravel:laravel /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

USER laravel

# Установка зависимостей Composer
RUN composer install --optimize-autoloader --no-interaction --no-progress

EXPOSE 9000

CMD ["php-fpm"]
