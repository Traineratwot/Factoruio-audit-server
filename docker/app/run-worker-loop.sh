#!/bin/bash

# Скрипт для запуска команды в бесконечном цикле
# Использование: /etc/run-worker-loop.sh "command with args"

set -e

if [ -z "$1" ]; then
    echo "Error: No command provided"
    echo "Usage: /etc/run-worker-loop.sh \"command with arguments\""
    exit 1
fi

COMMAND="$1"
PROCESS_NAME=$(basename "$(echo "$COMMAND" | awk '{print $1}')")

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting worker loop for: $COMMAND"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Process name: $PROCESS_NAME"

# Увеличим количество попыток перезапуска
RESTART_DELAY=1
MAX_RESTART_DELAY=60
FAIL_COUNT=0

while true; do
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Executing: $COMMAND"

    # Выполняем команду
    eval "$COMMAND" || {
        EXIT_CODE=$?
        FAIL_COUNT=$((FAIL_COUNT + 1))

        # Экспоненциальная задержка при частых падениях
        if [ $FAIL_COUNT -gt 3 ]; then
            RESTART_DELAY=$((RESTART_DELAY * 2))
            if [ $RESTART_DELAY -gt $MAX_RESTART_DELAY ]; then
                RESTART_DELAY=$MAX_RESTART_DELAY
            fi
            echo "[$(date '+%Y-%m-%d %H:%M:%S')] Worker failed $FAIL_COUNT times. Increasing restart delay to ${RESTART_DELAY}s"
        else
            RESTART_DELAY=1
        fi

        echo "[$(date '+%Y-%m-%d %H:%M:%S')] Worker exited with code $EXIT_CODE. Restarting in ${RESTART_DELAY}s..."
    }

    # Сбрасываем счетчик при успешном выполнении
    if [ $? -eq 0 ]; then
        FAIL_COUNT=0
        RESTART_DELAY=1
    fi

    sleep $RESTART_DELAY
done
