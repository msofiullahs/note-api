FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libsqlite3-dev libicu-dev \
    && docker-php-ext-install -j"$(nproc)" zip pdo pdo_sqlite intl bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts

COPY . .
RUN composer dump-autoload --optimize

EXPOSE 8000

CMD ["sh", "-c", "\
        [ -f .env ] || cp .env.example .env; \
        php artisan key:generate --force; \
        touch database/database.sqlite; \
        php artisan migrate --force --seed; \
        php artisan serve --host=0.0.0.0 --port=8000 \
    "]
