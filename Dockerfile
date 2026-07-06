# Stage 1: Composer dependencies
FROM composer:latest AS composer-base

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction \
    --ignore-platform-reqs


# Stage 2: Frontend build (bun)
FROM oven/bun:alpine AS frontend-builder

WORKDIR /app

COPY package.json bun.lock* ./
RUN bun install

COPY . .
RUN bun run build


# Stage 3: Main application (base)
FROM traineratwot/php:prod AS prod

WORKDIR /app

RUN apk add --no-cache \
    supervisor

COPY --from=oven/bun:alpine /usr/local/bin/bun /usr/local/bin/bun

COPY ./docker/app/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY ./docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY ./docker/app/post-startup.sh /usr/local/bin/post-startup.sh
COPY ./docker/app/start_server.sh /usr/local/bin/start_server.sh
COPY ./docker/app/run-worker-loop.sh /usr/local/bin/run-worker-loop.sh

RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    storage/logs \
    storage/app/public \
    bootstrap/cache \
    /var/log/supervisor \
    && chmod +x /usr/local/bin/entrypoint.sh \
              /usr/local/bin/post-startup.sh \
              /usr/local/bin/run-worker-loop.sh \
              /usr/local/bin/start_server.sh

COPY --from=composer-base --chown=www-data:www-data /app/vendor ./vendor

COPY --chown=www-data:www-data . .

# Копируем собранный фронтенд (перезаписываем public)
COPY --from=frontend-builder --chown=www-data:www-data /app/public ./public
COPY --from=frontend-builder --chown=www-data:www-data /app/bootstrap/ssr ./bootstrap/ssr

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && echo "Проверка совместимости зависимостей с текущей платформой..." \
    && composer check-platform-reqs || { \
        echo "ВНИМАНИЕ: Обнаружены несовместимости платформы!"; \
        echo "Попытка переустановки зависимостей..."; \
        composer install --optimize-autoloader --no-interaction || exit 1; \
    } \
    && composer dump-autoload --optimize --no-scripts

EXPOSE 80 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]


# Stage 4: Development
FROM traineratwot/php:dev AS dev

WORKDIR /app
RUN apk add --no-cache \
    supervisor

COPY --from=oven/bun:alpine /usr/local/bin/bun /usr/local/bin/bun

COPY ./docker/app/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY ./docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY ./docker/app/post-startup.sh /usr/local/bin/post-startup.sh
COPY ./docker/app/start_server.sh /usr/local/bin/start_server.sh
COPY ./docker/app/run-worker-loop.sh /usr/local/bin/run-worker-loop.sh

RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    storage/logs \
    storage/app/public \
    bootstrap/cache \
    /var/log/supervisor \
    && chmod +x /usr/local/bin/entrypoint.sh \
              /usr/local/bin/post-startup.sh \
              /usr/local/bin/run-worker-loop.sh \
              /usr/local/bin/start_server.sh

COPY --from=composer-base --chown=www-data:www-data /app/vendor ./vendor

COPY --chown=www-data:www-data . .

COPY ./.env ./.env

# Копируем собранный фронтенд (перезаписываем public)
COPY --from=frontend-builder --chown=www-data:www-data /app/public ./public
COPY --from=frontend-builder --chown=www-data:www-data /app/bootstrap/ssr ./bootstrap/ssr

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && composer check-platform-reqs || { \
        echo "ВНИМАНИЕ: Обнаружены несовместимости платформы!"; \
        composer install --optimize-autoloader --no-interaction || exit 1; \
    } \
    && composer dump-autoload --optimize --no-scripts

RUN composer global require traineratwot/json2dto \
    && ln -s /root/.composer/vendor/bin/json2dto /usr/local/bin/json2dto
EXPOSE 80 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
