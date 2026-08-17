# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1: vendor (Composer)
#
# Genera un vendor/ desechable, solo para poder arrancar la app Laravel y que
# Wayfinder (stage 2) pueda leer las rutas definidas y generar sus helpers de
# TypeScript. No se usa nada de este stage en la imagen final: el stage final
# corre su propio `composer install`.
# ---------------------------------------------------------------------------
FROM composer:latest AS vendor

WORKDIR /app

# Cache mount: persiste el cache de paquetes de Composer entre builds en la
# misma máquina, para no volver a descargar todo en cada rebuild local.
# No ayuda en un build 100% limpio (ej. CI sin cache previo), pero mitiga el
# rate limiting de GitHub (HTTP 429) durante el desarrollo iterativo.
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    set -eu; \
    attempt=1; \
    max_attempts=3; \
    until composer install --no-scripts --no-interaction --ignore-platform-reqs; do \
        if [ "$attempt" -ge "$max_attempts" ]; then \
            echo "composer install failed after ${max_attempts} attempts" >&2; \
            exit 1; \
        fi; \
        wait_seconds=$((attempt * 10)); \
        echo "composer install failed (attempt ${attempt}/${max_attempts}), retrying in ${wait_seconds}s..." >&2; \
        sleep "$wait_seconds"; \
        attempt=$((attempt + 1)); \
    done

COPY . .

RUN cp .env.example .env \
    && php artisan key:generate --no-interaction \
    && php artisan wayfinder:generate --with-form --no-interaction

# ---------------------------------------------------------------------------
# Stage 2: frontend (Node + Vite)
#
# node:22-alpine no tiene PHP, y el plugin de Vite de Wayfinder necesita
# `php artisan wayfinder:generate` para generar resources/js/{actions,routes,wayfinder}.
# Por eso se copian ya generados desde el stage "vendor" y se le indica a
# vite.config.ts (via WAYFINDER_SKIP) que no intente regenerarlos aqui.
# ---------------------------------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources/ resources/
COPY vite.config.ts tsconfig.json components.json ./

COPY --from=vendor /app/resources/js/routes ./resources/js/routes
COPY --from=vendor /app/resources/js/actions ./resources/js/actions
COPY --from=vendor /app/resources/js/wayfinder ./resources/js/wayfinder

ENV WAYFINDER_SKIP=1
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 3: public (assets estáticos para nginx)
#
# nginx no tiene PHP y ya no monta el código del host (ver docker-compose.yml),
# asi que necesita su propia copia de public/ -incluyendo los assets de Vite
# ya compilados- para poder servir archivos estáticos directamente desde su
# filesystem. Se arma en un stage aparte, sin PHP, para no depender de la
# imagen pesada del stage "app" solo para copiar unos archivos estáticos.
# ---------------------------------------------------------------------------
FROM alpine:3.20 AS public

WORKDIR /var/www/html

COPY public/ ./public/
COPY --from=frontend /app/public/build ./public/build

# ---------------------------------------------------------------------------
# Stage 4: app (imagen final, PHP-FPM)
# ---------------------------------------------------------------------------
FROM php:8.4-fpm AS app

# Dependencias del sistema necesarias para compilar extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP que Laravel + MySQL + Excel necesitan
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Composer (copiado desde la imagen oficial)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Fuerza permisos normales (755, sin sticky bit) por si la imagen base o el
# entorno de build dejan /var/www/html con modos inusuales (ej. drwxrwxrwt),
# lo que impediría escribir sobre el .env bind-monteado en tiempo de
# ejecución.
RUN chmod 755 /var/www/html

ENV COMPOSER_CACHE_DIR=/tmp/composer-cache

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    set -eu; \
    attempt=1; \
    max_attempts=3; \
    until composer install --no-scripts --no-autoloader --no-interaction; do \
        if [ "$attempt" -ge "$max_attempts" ]; then \
            echo "composer install failed after ${max_attempts} attempts" >&2; \
            exit 1; \
        fi; \
        wait_seconds=$((attempt * 10)); \
        echo "composer install failed (attempt ${attempt}/${max_attempts}), retrying in ${wait_seconds}s..." >&2; \
        sleep "$wait_seconds"; \
        attempt=$((attempt + 1)); \
    done

COPY . .
RUN composer dump-autoload --optimize

COPY --from=frontend /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# ---------------------------------------------------------------------------
# Stage 5: nginx (imagen final, sirve estáticos y proxea PHP al stage "app")
# ---------------------------------------------------------------------------
FROM nginx:alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=public /var/www/html/public /var/www/html/public