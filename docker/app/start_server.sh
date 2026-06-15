#!/bin/bash
set -e

# Функция для graceful shutdown
cleanup() {
    echo "Shutting down Laravel Octane..."
    kill -TERM "$PID" 2>/dev/null || true
    wait "$PID" 2>/dev/null || true
    exit 0
}

trap cleanup SIGTERM SIGINT

echo "Starting Laravel server..."

if [ "$APP_ENV" = "production" ]; then
    echo "Production mode: Starting serve..."
    php /app/artisan serve \
        --host=0.0.0.0 \
        --port=9000
else
    echo "Development mode: Starting serve..."
    php /app/artisan serve \
        --host=0.0.0.0 \
        --port=9000
fi

PID=$!
wait "$PID"
