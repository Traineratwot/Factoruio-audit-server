#!/bin/bash
set -e

echo "Running post-startup tasks..."

# Создание символической ссылки для storage (если не существует)
echo "Creating storage link..."
php /app/artisan storage:link 2>/dev/null || true

# Запуск миграций
echo "Running migrations..."
php /app/artisan migrate --force || {
    echo "Warning: Migrations failed, continuing anyway..."
}

echo "Cleaning cache..."
php /app/artisan startup:optimize --clear --force || true

# Очистка и кеширование конфигурации только для production
if [ "$APP_ENV" = "production" ]; then
    echo "Production environment detected - optimizing application..."
    php /app/artisan startup:optimize --force  || true

    php /app/artisan migrate --force || true
fi

# Опционально: очистка старых логов
echo "Cleaning old logs..."
find /app/storage/logs -name "*.log" -type f -print0 | xargs -0 truncate -s 0 2>/dev/null || true

#php /app/artisan octane:reload

echo "Post-startup tasks completed!"
