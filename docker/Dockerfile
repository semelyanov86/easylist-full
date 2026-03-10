# =============================================================================
# Этап 1: Vendor — установка Composer-зависимостей
# =============================================================================
FROM composer:latest AS vendor
WORKDIR /app

ADD --chmod=0755 \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/
RUN install-php-extensions gd bcmath intl pcntl redis pdo_mysql

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress

# =============================================================================
# Этап 2: Codegen — генерация Wayfinder TypeScript-типов через PHP artisan
# =============================================================================
FROM composer:latest AS codegen

COPY --from=vendor /usr/local/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath pdo_sqlite

WORKDIR /app
COPY . /app

# Устанавливаем ВСЕ зависимости включая dev (нужен laravel/boost и др.)
RUN composer install --prefer-dist --no-interaction --no-scripts --no-progress

# Минимальный .env без реальной БД — wayfinder читает только маршруты
RUN printf 'APP_KEY=base64:%s\nAPP_ENV=production\nDB_CONNECTION=sqlite\nDB_DATABASE=/tmp/db.sqlite\n' \
        "$(head -c 32 /dev/urandom | base64)" > .env \
    && touch /tmp/db.sqlite \
    && mkdir -p storage/framework/views \
              storage/framework/cache/data \
              storage/framework/sessions \
              storage/logs \
              bootstrap/cache

RUN php artisan wayfinder:generate --with-form

# =============================================================================
# Этап 3: Assets — сборка фронтенда (Node 22 + npm)
# =============================================================================
FROM node:22-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

# Заглушка php: vite-plugin-wayfinder вызывает artisan при сборке,
# файлы уже сгенерированы на этапе codegen — просто даём плагину не упасть
RUN printf '#!/bin/sh\nexit 0\n' > /usr/local/bin/php && chmod +x /usr/local/bin/php

COPY vite.config.ts tsconfig.json ./
COPY resources ./resources
COPY public ./public

# Сгенерированные Wayfinder-типы из codegen-этапа
COPY --from=codegen /app/resources/js/actions ./resources/js/actions
COPY --from=codegen /app/resources/js/routes ./resources/js/routes

ENV NODE_ENV=production
RUN npm run build

# =============================================================================
# Этап 4: Worker — PHP CLI (очереди, планировщик, Pulse)
# =============================================================================
FROM php:8.5-cli-alpine AS worker

COPY --from=vendor /usr/local/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath intl pcntl gd curl pdo_mysql mbstring redis

ARG APP_ENV=production
WORKDIR /app

COPY . /app
COPY ".env.${APP_ENV}" .env
COPY --from=vendor /app/vendor /app/vendor

RUN mkdir -p storage/framework/views \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/logs \
        bootstrap/cache \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www-data
CMD ["php", "artisan", "queue:work", "--tries=3", "--sleep=1"]

# =============================================================================
# Этап 5: FrankenPHP — веб-сервер (Octane)
# =============================================================================
FROM dunglas/frankenphp:1.12.1-php8.5 AS frankenphp
WORKDIR /app

ADD --chmod=0755 \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/
RUN install-php-extensions bcmath intl pcntl gd curl pdo_mysql mbstring redis

ARG APP_ENV=production
COPY . /app
COPY ".env.${APP_ENV}" .env
COPY --from=vendor /app/vendor /app/vendor
COPY --from=assets /app/public/build /app/public/build

# Гарантируем актуальный worker-файл из стаба текущей версии Octane
RUN cp vendor/laravel/octane/src/Commands/stubs/frankenphp-worker.php public/frankenphp-worker.php

RUN mkdir -p storage/framework/views \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/logs \
        bootstrap/cache \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=9000", "--workers=8", "--log-level=info"]

# =============================================================================
# Этап 6: FrankenPHP-dev — с dev-зависимостями (для локальной разработки)
# =============================================================================
FROM frankenphp AS frankenphp-dev

# Заменяем vendor на полный (с dev-зависимостями) из codegen-этапа
COPY --from=codegen /app/vendor /app/vendor
RUN php artisan package:discover --ansi
