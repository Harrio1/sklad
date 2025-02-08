# Этап 1: Сборка фронтенда
FROM node:20-alpine as frontend

WORKDIR /app

COPY package*.json ./

# Очистка кэша npm и установка зависимостей с дополнительными флагами
RUN rm -rf node_modules package-lock.json && \
    npm cache clean --force && \
    npm install --no-optional --legacy-peer-deps

COPY . .

# Установка специфических зависимостей для Alpine
RUN apk add --no-cache python3 make g++ && \
    npm rebuild && \
    npm run build

# Этап 2: Установка PHP зависимостей
FROM composer:2.6 as composer

WORKDIR /app

COPY composer*.json ./
COPY composer*.lock ./

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

# Этап 3: Финальный образ
FROM php:8.2.15-fpm-alpine

# Установка Node.js и npm
RUN apk add --no-cache \
    nodejs \
    npm \
    make \
    g++

# Установка необходимых расширений PHP
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    opcache

# Копирование и настройка php.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Установка рабочей директории
WORKDIR /var/www/html

# Копирование файлов приложения
COPY . .
COPY --from=composer /app/vendor/ ./vendor/
COPY --from=frontend /app/public/build/ ./public/build/

# Установка npm зависимостей и сборка
RUN rm -rf node_modules package-lock.json && \
    npm cache clean --force && \
    npm install --no-optional --legacy-peer-deps && \
    npm run build

# Установка прав на директории
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Копирование и настройка конфигурационного файла
COPY .env.example .env
RUN php artisan key:generate

# Очистка кэша и оптимизация
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Открываем порт для PHP-FPM
EXPOSE 9000

# Запускаем PHP-FPM
CMD ["php-fpm"]


