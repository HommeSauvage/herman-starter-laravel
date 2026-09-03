# syntax=docker/dockerfile:1
# Reference production Dockerfile — committed by the Clique hosting setup
# exactly as the in-app agent will. Builds are Dockerfile-agnostic: the
# project ships its own Dockerfile; the platform clones at a pinned ref
# and runs `docker build` against it.
#
# Multi-stage: bun/vite build of the Inertia frontend (bun.lock is the
# lockfile) -> FrankenPHP runtime with pdo_sqlite + Litestream replication.
# Base images are pinned by version tag; bump deliberately.

# ---- Frontend build stage ----
FROM oven/bun:1.4.0-alpine AS assets
# Laravel Wayfinder generates route types from the PHP app AT BUILD TIME
# (the vite plugin runs `php artisan wayfinder:generate --with-form`) — the
# assets stage needs PHP + the composer vendor tree, not just bun. All prod
# deps (wayfinder is in `require`, not require-dev).
RUN apk add --no-cache \
  php84-cli php84-phar php84-mbstring php84-xml php84-dom php84-xmlwriter \
  php84-tokenizer php84-ctype php84-curl php84-fileinfo php84-iconv \
  php84-openssl php84-session php84-simplexml php84-pdo php84-pdo_sqlite \
  php84-sqlite3 php84-intl php84-zip
# Alpine versions the binary (php84) — composer + artisan expect `php`.
RUN ln -sf /usr/bin/php84 /usr/bin/php
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
# --no-scripts: artisan isn't in the tree yet (package:discover fires on
# post-autoload-dump); it runs below once the full app is copied.
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts
COPY package.json bun.lock ./
RUN bun install --frozen-lockfile --no-progress
COPY . .
# Fresh clones lack Laravel's runtime dirs; artisan (wayfinder) needs them.
RUN mkdir -p storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/app/public storage/app/private
RUN composer dump-autoload --optimize
# artisan wants an .env to boot (key never leaves the build container).
RUN cp .env.example .env && php artisan key:generate --force --no-interaction
RUN bun run build

# ---- Runtime stage ----
FROM dunglas/frankenphp:php8.4-bookworm
WORKDIR /app

# Composer is not in the frankenphp image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# SQLite (the tenant data plane) + Laravel's usual stack.
RUN install-php-extensions pdo_sqlite sqlite3 opcache intl zip

# Litestream at a pinned version (static amd64 binary — the fleet + deploy
# runner are amd64; revisit arch if the fleet goes multi-arch).
ARG LITESTREAM_VERSION=0.3.13
ADD https://github.com/benbjohnson/litestream/releases/download/v${LITESTREAM_VERSION}/litestream-v${LITESTREAM_VERSION}-linux-amd64.tar.gz /tmp/litestream.tar.gz
RUN tar -xzf /tmp/litestream.tar.gz -C /usr/local/bin litestream \
    && rm /tmp/litestream.tar.gz

# Composer deps before the app code (layer caching), no scripts/autoload yet.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress

COPY . .
COPY --from=assets /app/public/build ./public/build
COPY litestream.yml /etc/litestream.yml
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN mkdir -p storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/app/public storage/app/private \
    && composer dump-autoload --optimize \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

# FrankenPHP classic mode on :80 — Laravel's /up route answers kamal-proxy's
# default health check. Runtime env arrives via the deploy config (env files),
# never as build args.
ENV SERVER_NAME=:80
EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
