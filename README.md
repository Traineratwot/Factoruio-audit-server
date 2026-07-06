# Factorio Audit

Laravel 13 + React 19 (Inertia.js v3) приложение для аудита модов Factorio через Factorio Mod Portal API.

## Стек

- **Backend**: Laravel 13, PHP 8.3+, PostgreSQL 18, Redis, Meilisearch
- **Frontend**: React 19, Inertia.js v3, TypeScript, Tailwind CSS v4, Vite 8
- **Очереди**: Laravel Queues + Supervisor (в Docker)
- **Поиск**: Meilisearch через Laravel Scout
- **Админка**: Filament v5
- **SSR**: Inertia SSR (Bun runtime, supervisord)
- **Docker**: multi-stage Dockerfile (dev/prod), docker-compose.dev.yml

## Требования

- Docker + Docker Compose
- Bun (для локальных команд, или использовать bun внутри контейнера)

## Первый запуск

```bash
make init
```

Это выполнит: `bun install` → копирование `.env` → `docker compose build` → `migrate` → `seed` → `link`.

## Процесс разработки

### Запуск контейнеров

```bash
make up          # запустить все контейнеры
make down        # остановить
make restart     # перезапуск
```

### Frontend (React/TypeScript)

**Важно: `bun run dev` нужно запускать ВНУТРИ контейнера, а не на хосте!**

Порт 5273 уже занят пробросом из Docker (`ports: "5273:5273"`), поэтому `bun run dev` на хосте упадёт с ошибкой `Port 5273 is already in use`.

```bash
# Запуск Vite dev-сервера с HMR (внутри контейнера):
make dev

# Или напрямую:
docker compose -f docker-compose.dev.yml exec app bun run dev
```

После запуска Vite dev-сервера изменения в `.tsx` файлах автоматически появляются в браузере через HMR (Hot Module Replacement) — без перезагрузки страницы.

**Если HMR не нужен** (production-сборка):

```bash
bun run build                    # на хосте — собирает client + SSR бандлы
make restart                     # перезапуск контейнера для подхвата нового SSR-бандла
```

### Backend (PHP/Laravel)

```bash
make bash                        # консоль внутри контейнера
make psql                        # PostgreSQL консоль
make tinker                      # Laravel Tinker
```

### Инструменты разработки

```bash
# Линтинг JS/TS
bun run lint                     # автоФикс
bun run lint:check               # только проверка
bun run format                   # форматирование
bun run format:check             # проверка форматирования
bun run types:check              # TypeScript проверка типов

# Линтинг PHP (внутри контейнера)
composer lint                    # Pint — автоФикс
composer lint:check              # Pint — только проверка
composer types:check             # PHPStan level 7
composer test                    # lint + types + tests

# Полная CI-проверка
composer ci:check
```

### Тесты

```bash
# Все тесты
make test

# Один файл
docker compose -f docker-compose.dev.yml exec -e APP_ENV=testing app php artisan test tests/Feature/SomeTest.php
```

## Архитектура SSR

Приложение использует **Inertia SSR** (Server-Side Rendering):

- SSR-сервер запускается автоматически через **supervisord** (`php artisan inertia:start-ssr`) на порту 13714
- SSR-бандл: `bootstrap/ssr/ssr.js` (собирается командой `vite build --ssr`)
- Клиентский бандл: `public/build/` (собирается командой `vite build`)
- Команда `bun run build` делает оба шага: `vite build && vite build --ssr`

### SSR и разработка

**Vite HMR обновляет только клиентскую часть.** SSR-сервер загружает бандл при старте и не видит изменения в исходниках.
Meta-теги (`og:title`, `twitter:image` и т.д.) рендерятся сервером из SSR-бандла — они не обновляются через HMR.

**Для разработки** рекомендуется отключить SSR в `.env.local`:

```
INERTIA_SSR_ENABLED=false
```

Затем `make restart`. Все изменения (включая meta-теги) будут отображаться мгновенно через клиентский HMR.

**Для тестирования SSR** — пересобрать и перезапустить:

```bash
bun run build
docker compose -f docker-compose.dev.yml exec app bash -c 'kill $(pgrep -f "inertia:start-ssr")'
# supervisord автоматически перезапустит SSR с новым бандлом
```

## Структура проекта

```
app/
  Console/          # Artisan-команды
  Filament/         # Админка (Filament v5)
  Http/
    Controllers/
      ModController.php       # каталог, репорт, поиск
      AuditController.php     # поиск (DB), версии, запуск аудита
  Jobs/
    AuditJob.php              # очередь: AuditService::audit()
  Models/
    Mod.php                   # моды, репорты, аудит
    Report.php                # результаты аудита
    ModVersion.php            # версии модов
    Author.php                # авторы
  Services/
    AuditService.php          # WebSocket JSON-RPC клиент для внешнего сканера
    FactorioService.php       # клиент Factorio Mod Portal API (кеш 1ч)

resources/js/
  app.tsx                     # точка входа Inertia
  ssr.tsx                     # точка входа SSR
  pages/
    welcome.tsx               # каталог модов
    report.tsx                # страница отчёта
  components/
    mods/                     # компоненты каталога
    AuditReport/              # компоненты отчёта
    ui/                       # общие UI компоненты
  hooks/                      # React хуки
  types/                      # TypeScript типы
  utils/                      # утилиты
```

## Маршруты

```
GET  /                                    → каталог модов
GET  /report/mod/{mod}                    → отчёт (последняя версия)
GET  /report/mod/{mod}/version/{version}  → отчёт конкретной версии
GET  /api/mods/search                     → поиск модов (DB)
GET  /api/mods/{mod}/versions             → версии мода
POST /audit                               → запуск аудита (rate limit: 5/ч)
```

## Сервисы Docker

| Сервис    | Порт (хост) | Описание              |
|-----------|-------------|-----------------------|
| app       | 8100        | Laravel + PHP         |
| app       | 5273        | Vite dev-сервер       |
| postgres  | 5532        | PostgreSQL 18         |
| search    | 9103        | Meilisearch           |
| mailhog   | 8125 / 1125 | UI / SMTP             |

## Частые проблемы

### `bun run dev` — Port 5273 is already in use

Порт занят Docker-контейнером. Запускайте dev-сервер **внутри** контейнера:

```bash
make dev
```

### Изменения не отображаются (meta-теги, SSR-контент)

SSR-сервер загружает бандл при старте и не видит изменения в исходниках. Два варианта:

1. **Отключить SSR для разработки** (рекомендуется) — добавить `INERTIA_SSR_ENABLED=false` в `.env.local`, затем `make restart`
2. **Пересобрать SSR-бандл** — `bun run build`, затем `kill $(pgrep -f "inertia:start-ssr")` внутри контейнера (supervisor перезапустит)

### `inertia:start-ssr` — port already in use

SSR-сервер уже работает через supervisord. Не нужно запускать его вручную.

### SSR warning: bundle not found at ssr.mjs

Безопасное предупреждение — Inertia ищет `ssr.mjs`, не находит, использует `ssr.js`. Работает корректно.

## Git hooks

```bash
make install-hooks    # установка pre-commit (pint --dirty)
```
