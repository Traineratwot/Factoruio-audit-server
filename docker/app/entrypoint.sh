#!/bin/bash
set -e


echo "Starting Laravel application..."

# Создание всех необходимых директорий для storage
echo "Creating storage directories..."
mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/framework/testing
mkdir -p /app/storage/logs
mkdir -p /app/storage/app/public
mkdir -p /app/bootstrap/cache

# Создание директорий для логов supervisor
echo "Creating supervisor log directories..."
mkdir -p /var/log/supervisor
mkdir -p /app/storage/logs/supervisor

# Установка правильных прав доступа
echo "Setting permissions..."
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo "Выполнение composer post-autoload-dump скриптов..."
composer install

echo "Application ready!"

# Запуск Supervisor
echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
