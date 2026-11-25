# Tahap 1: build aset frontend (Vite + Tailwind) dengan Node.
# public/build tidak ada di repo, jadi harus dibangun saat image dibuat.
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# Runtime PHP-FPM. Aset frontend (public/build) sudah dibangun di tahap
# frontend di atas dan disalin lewat COPY --from=frontend.
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Naikkan batas memori PHP (default image hanya 128M — kurang untuk
# render PDF kartu ujian yang memuat banyak gambar base64 sekaligus).
RUN echo 'memory_limit = 512M' > /usr/local/etc/php/conf.d/memory-limit.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY --from=frontend /app/public/build ./public/build

# Install dependensi dari Packagist default + retry loop.
# Tidak memakai mirror pihak ketiga (sering timeout) dan tidak memakai
# "composer update --lock" (memakan waktu lama) — langsung install dari lock.
RUN composer config -g audit.block-insecure false \
    && for i in 1 2 3 4 5; do \
        composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader && break || sleep 10; \
    done \
    && test -f vendor/autoload.php

RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
