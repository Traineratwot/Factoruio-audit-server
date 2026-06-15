#!/bin/bash
set -euo pipefail

# ── Конфигурация ──────────────────────────────────────────────
DOCKER_USERNAME="traineratwot"
IMAGE_NAME="php"
VERSION="1.0.0"
PLATFORMS="linux/amd64,linux/arm64"
DOCKERFILE="./docker/base/Dockerfile"
BUILDER_NAME="multiplatform-builder"

# ── Цвета ─────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
info()  { echo -e "${GREEN}[INFO]${NC}  $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; }

# ── Проверки ──────────────────────────────────────────────────
command -v docker  &>/dev/null || { error "Docker не установлен!"; exit 1; }
docker buildx version &>/dev/null || { error "Docker Buildx не установлен!"; exit 1; }

# ── Buildx builder ────────────────────────────────────────────
info "Настройка Docker Buildx..."
if ! docker buildx inspect "$BUILDER_NAME" &>/dev/null; then
    info "Создание нового builder..."
    docker buildx create --name "$BUILDER_NAME" --use
else
    docker buildx use "$BUILDER_NAME"
fi
# ── Логин ─────────────────────────────────────────────────────
#info "Вход в Docker Hub..."
#if [ -z "${DOCKER_PASSWORD:-}" ]; then
#    docker login -u "$DOCKER_USERNAME"
#else
#    echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME"
#fi

FULL="${DOCKER_USERNAME}/${IMAGE_NAME}"

# ── Сборка prod (= latest) ───────────────────────────────────
info "Сборка PROD образа для платформ: ${PLATFORMS}"
warn "Это может занять продолжительное время..."

docker buildx build \
    --platform "$PLATFORMS" \
    --file "$DOCKERFILE" \
    --target prod \
    --tag "${FULL}:prod" \
    --tag "${FULL}:latest" \
    --push \
    .

info "✅ prod / latest опубликованы"

# ── Сборка dev ────────────────────────────────────────────────
info "Сборка DEV образа для платформ: ${PLATFORMS}"

docker buildx build \
    --platform "$PLATFORMS" \
    --file "$DOCKERFILE" \
    --target dev \
    --tag "${FULL}:dev" \
    --push \
    .

info "✅ dev опубликован"

# ── Итого ─────────────────────────────────────────────────────
info "Опубликованные теги:"
info "  ${FULL}:latest"
info "  ${FULL}:prod"
info "  ${FULL}:dev"

info "Информация об образе:"
docker buildx imagetools inspect "${FULL}:latest"

info "🎉 Готово! https://hub.docker.com/r/${FULL}"
