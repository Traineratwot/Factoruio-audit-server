#!/bin/bash

set -e

# ============================================================================
# КОНФИГУРАЦИЯ
# ============================================================================

CONTAINER_NAME="${1:-app}"
MAX_ATTEMPTS="${2:-120}"
WAIT_SECONDS="${3:-1}"

# Цвета
INFO='\033[33m'
SUCCESS='\033[32m'
ERROR='\033[31m'
WARNING='\033[35m'
RESET='\033[0m'

# ============================================================================
# ФУНКЦИИ
# ============================================================================

# Очистка строки (кроссплатформенная)
clear_line() {
    printf "\r\033[K"
}

# Вывод прогресса
print_progress() {
    local current=$1
    local total=$2
    local status=$3
    local percentage=$((current * 100 / total))

    clear_line
    printf "${INFO}[%3d/%d] %3d%% | Status: %-15s${RESET}" \
        "$current" "$total" "$percentage" "$status"
}

# Проверка здоровья контейнера
check_container_health() {
    local container=$1

    # Проверка существования контейнера
    if ! docker ps -a --format '{{.Names}}' | grep -q "^${container}$"; then
        echo "not_found"
        return 1
    fi

    # Проверка, запущен ли контейнер
    local running
    running=$(docker inspect --format='{{.State.Running}}' "$container" 2>/dev/null)

    if [ "$running" != "true" ]; then
        echo "stopped"
        return 1
    fi

    # Получение статуса здоровья
    local health_status
    health_status=$(docker inspect --format='{{.State.Health.Status}}' "$container" 2>/dev/null)

    # Если healthcheck не настроен, считаем контейнер здоровым если он запущен
    if [ -z "$health_status" ] || [ "$health_status" = "<no value>" ]; then
        echo "healthy"
        return 0
    fi

    echo "$health_status"

    if [ "$health_status" = "healthy" ]; then
        return 0
    fi

    return 1
}

# Вывод информации
print_info() {
    echo -e "${INFO}$1${RESET}"
}

print_success() {
    echo -e "${SUCCESS}$1${RESET}"
}

print_error() {
    echo -e "${ERROR}$1${RESET}"
}

# Вывод системной информации
print_system_info() {
    echo ""
    print_info "═══════════════════════════════════════════════════════"
    print_info "Информация о системе:"
    print_info "OS: $(uname -s)"
    print_info "Контейнер: $CONTAINER_NAME"
    print_info "Максимум попыток: $MAX_ATTEMPTS"
    print_info "Интервал проверки: ${WAIT_SECONDS}s"
    print_info "═══════════════════════════════════════════════════════"
    echo ""
}

# Основная функция ожидания
wait_for_container() {
    local start_time
    start_time=$(date +%s)

    for i in $(seq 1 "$MAX_ATTEMPTS"); do
        local status
        status=$(check_container_health "$CONTAINER_NAME")
        local exit_code=$?

        # Контейнер здоров
        if [ $exit_code -eq 0 ] && [ "$status" = "healthy" ]; then
            local end_time
            end_time=$(date +%s)
            local elapsed=$((end_time - start_time))

            clear_line
            print_success "✓ Контейнер готов (попытка $i/$MAX_ATTEMPTS, прошло ${elapsed}s)"
            return 0
        fi

        # Вывод прогресса
        print_progress "$i" "$MAX_ATTEMPTS" "$status"
        sleep "$WAIT_SECONDS"
    done

    # Таймаут истек
    local end_time
    end_time=$(date +%s)
    local elapsed=$((end_time - start_time))

    clear_line
    print_error "✗ Контейнер не стал healthy за $elapsed сек"
    return 1
}

# Обработка сигналов
cleanup() {
    clear_line
    print_error "Прервано пользователем"
    exit 130
}

trap cleanup INT TERM

# ============================================================================
# ОСНОВНАЯ ПРОГРАММА
# ============================================================================

main() {
    print_system_info

    print_info "Ожидание здорового состояния контейнера..."
    echo ""

    if wait_for_container; then
        echo ""
        print_success "Все контейнеры готовы к работе!"
        return 0
    else
        echo ""
        print_error "Контейнер не готов, но продолжаем..."
        return 0
    fi
}

# Запуск
main
exit $?
