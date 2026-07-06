.PHONY: help bash dev build up down restart logs shell migrate seed test composer cache optimize dump restore wipe redb swagger fix link serve destroy ps stop redis-cli psql mailhog minio queue queue-restart tinker

# Defines
INFO = \033[33m
SUCCESS = \033[32m
RESET = \033[0m

# Подключение .env с приоритетом
ifneq (,$(wildcard ./.env.local))
	include .env.local
	export
else
	ifneq (,$(wildcard ./.env))
		include .env
		export
	endif
endif

# Docker Compose команда с env файлом
DOCKER_COMPOSE = docker compose -f docker-compose.dev.yml

bash: # Открывает консоль внутри контейнера `app`.
	$(DOCKER_COMPOSE) exec app bash
dev: # Запускает Vite dev-сервер с HMR внутри контейнера.
	$(DOCKER_COMPOSE) exec app bash -c 'bun run dev'

help: # Показать справку по Makefile.
	@grep -E '^[a-zA-Z0-9 -]+:.*#'  Makefile | sort | while read -r l; do printf "\033[1;32m$$(echo $$l | cut -f 1 -d':')\033[00m:$$(echo $$l | cut -f 2- -d'#')\n"; done

init: # Первый запуск сборки
	@bun i
	@make install-hooks
	@cp .env .env.local
	# Копируем .env в .env.local с заменой значения APP_URL
	@sed "s|^APP_URL=.*|APP_URL=http://localhost:$${PUB_APP_PORT}|" .env > .env.local
	@cp docker-compose.dev.yml docker-compose.yml
	@echo "$(SUCCESS)✅ Файлы скопированы: .env.local, docker-compose.yml$(RESET)"
	@make remake

remake: # Перезапускает проект, выполняя несколько команд: down, install, connect-db, migrate, cache, seed. [CI]
	@make down
	@make up
	@make connect-db
	@make migrate
	@make cache
	@make seed
	@make cron
	@make link

build: # Собирает образы для контейнеров.
	@echo "$(INFO)Сборка образов$(RESET)"
	$(DOCKER_COMPOSE) build

build-clear: # Собирает образы для контейнеров без кеша.
	@echo "$(INFO)Сборка образов без кеша$(RESET)"
	$(DOCKER_COMPOSE) build --no-cache

up: # Запускает контейнеры в фоновом режиме.
	@echo "$(INFO)Запуск контейнеров$(RESET)"
	$(DOCKER_COMPOSE) up -d
	@make wait-healthy

down: # Останавливает и удаляет контейнеры.
	@echo "$(INFO)Остановка контейнеров$(RESET)"
	$(DOCKER_COMPOSE) down

stop: # Останавливает контейнеры без их удаления.
	@echo "$(INFO)Пауза контейнеров$(RESET)"
	$(DOCKER_COMPOSE) stop

restart: # Перезапускает контейнеры.
	@echo "$(INFO)Перезапуск контейнеров$(RESET)"
	@make down
	@make up
	@make link

reload: # Перезапуск octane
	@echo "$(INFO)Перезапуск octane$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan octane:reload

logs: # Показывает логи контейнеров.
	$(DOCKER_COMPOSE) logs -f

shell: # Открывает консоль внутри контейнера `app` (алиас для bash).
	@make bash

ps: # Показывает статус контейнеров.
	$(DOCKER_COMPOSE) ps

destroy: # Уничтожает контейнеры, удаляет тома и осиротевшие контейнеры.
	@echo "$(INFO)Уничтожение контейнеров$(RESET)"
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

destroy-all: # Полностью уничтожает контейнеры, удаляет все образы, тома и осиротевшие контейнеры.
	@echo "$(INFO)Полное уничтожение контейнеров$(RESET)"
	$(DOCKER_COMPOSE) down --rmi all --volumes --remove-orphans

composer: # Устанавливает PHP зависимости с помощью Composer.
	@echo "$(INFO)Установка PHP зависимостей$(RESET)"
	$(DOCKER_COMPOSE) exec app composer install

migrate: # Запускает миграции базы данных.
	@echo "$(INFO)Запуск миграций$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan migrate

seed: # Запускает сиды базы данных.
	@echo "$(INFO)Запуск сидов$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan db:seed --force

test: # Запускает тесты в среде `testing`.
	@echo "$(INFO)Запуск тестов$(RESET)"
	$(DOCKER_COMPOSE) exec -e APP_ENV=testing app php artisan test $(TEAMCITY_REPORT)

cache: # Очищает кеш приложения.
	@echo "$(INFO)Очистка кеша$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan startup:optimize --clear --force

optimize: # Оптимизирует приложение.
	@echo "$(INFO)Оптимизация приложения$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan startup:optimize

wipe: # Уничтожает базу данных.
	@echo "$(INFO)Уничтожение базы данных$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan db:wipe

redb: # Уничтожает базу и создает с нуля, используй для проверки миграций [dev]
	@echo "$(INFO)Пересоздание базы данных$(RESET)"
	$(DOCKER_COMPOSE) exec app bash -c 'php artisan db:wipe && php artisan migrate && php artisan db:seed --force'
	$(DOCKER_COMPOSE) exec app bash -c 'php artisan db:seed'

dump: # Создает дамп текущей базы данных в папку `docker/postgres/dumps`.
	@echo "$(INFO)Дамп текущей базы в папку docker/postgres/dumps$(RESET)"
	@mkdir -p docker/postgres/dumps
	$(DOCKER_COMPOSE) exec -T postgres pg_dump -U $(DB_USERNAME) $(DB_DATABASE) > docker/postgres/dumps/dump_$$(date +%Y%m%d_%H%M%S).sql
	@echo "$(SUCCESS)Дамп создан успешно$(RESET)"

restore: # Восстанавливает базу данных из последнего дампа в папке `docker/postgres/dumps`.
	@echo "$(INFO)Восстановление из docker/postgres/dumps$(RESET)"
	@LATEST_DUMP=$$(ls -t docker/postgres/dumps/*.sql 2>/dev/null | head -1); \
	if [ -z "$$LATEST_DUMP" ]; then \
		echo "$(INFO)Дампы не найдены$(RESET)"; \
		exit 1; \
	fi; \
	echo "$(INFO)Восстановление из $$LATEST_DUMP$(RESET)"; \
	$(DOCKER_COMPOSE) exec -T postgres psql -U $(DB_USERNAME) -d $(DB_DATABASE) < "$$LATEST_DUMP"
	@make migrate
	@make cache
	@echo "$(SUCCESS)Восстановление завершено$(RESET)"

pgrestore: # Восстанавливает базу данных из последнего дампа через pg_restore (custom-format).
	@echo "$(INFO)Восстановление (pg_restore) из docker/postgres/dumps$(RESET)"
	@LATEST_DUMP=$$(ls -t docker/postgres/dumps/*.sql 2>/dev/null | head -1); \
	if [ -z "$$LATEST_DUMP" ]; then \
		echo "$(INFO)Дампы не найдены$(RESET)"; \
		exit 1; \
	fi; \
	echo "$(INFO)Восстановление из $$LATEST_DUMP$(RESET)"; \
	$(DOCKER_COMPOSE) exec -T postgres pg_restore \
		-U $(DB_USERNAME) \
		-d $(DB_DATABASE) \
		--clean \
		--if-exists \
		--no-owner \
		--no-privileges \
		--verbose < "$$LATEST_DUMP"
	@make migrate
	@make cache
	@echo "$(SUCCESS)Восстановление завершено$(RESET)"
swagger: # Создает swagger документацию.
	@echo "$(INFO)Генерация Swagger$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan l5-swagger:generate

fix: # Исправляет код в соответствии со стандартами кодирования [dev]
	@echo "$(INFO)Устаревший метод$(RESET)"
	@make pint

wait-healthy: # Ожидает, пока контейнер app станет healthy.
	@chmod +x docker/scripts/wait-healthy.sh && \
	CONTAINER_NAME=$$($(DOCKER_COMPOSE) ps app --format '{{.Name}}') && \
	docker/scripts/wait-healthy.sh "$$CONTAINER_NAME"

link: # Печатает ссылки на все сервисы [dev]
	@echo "$(SUCCESS)========================================$(RESET)"
	@echo "$(SUCCESS)  Сервисы доступны по адресам:$(RESET)"
	@echo "$(SUCCESS)========================================$(RESET)"
	@echo "$(SUCCESS)Приложение:        http://localhost:$(PUB_APP_PORT)$(RESET)"
	@echo "$(SUCCESS)Приложение admin:  http://localhost:$(PUB_APP_PORT)/admin user: $${FILAMENT_ROOT_USER} pass: $${FILAMENT_ROOT_PASSWORD} $(RESET)"
	@echo "$(SUCCESS)PgAdmin UI:        http://localhost:$(PUB_PGADMIN_PORT) admin_pass: $${PGADMIN_PASSWORD:-admin}$ | DB_pass: $${DB_PASSWORD} $(RESET)"
	@echo "$(SUCCESS)MailHog UI:        http://localhost:$(PUB_MAILHOG_UI_PORT)$(RESET)"
	@echo "$(SUCCESS)Meilisearch UI:    http://localhost:$(PUB_SEARCH_PORT) pass: $${MEILISEARCH_KEY} $(RESET)"
	@echo "$(SUCCESS)MinIO Console:     http://localhost:$(PUB_MINIO_CONSOLE_PORT) user: $${MINIO_ROOT_USER} pass: $${MINIO_ROOT_PASSWORD} $(RESET)"
	@echo "$(SUCCESS)ImgProxy     :     http://localhost:18080 $(RESET)"
	@echo "$(SUCCESS)----------------------------------------$(RESET)"
	@echo "$(INFO)PostgreSQL:    localhost:$(PUB_POSTGRES_PORT)$(RESET)"
	@echo "$(INFO)Redis:         localhost:$(PUB_REDIS_PORT)$(RESET)"
	@echo "$(INFO)MailHog SMTP:  localhost:$(PUB_MAILHOG_SMTP_PORT)$(RESET)"
	@echo "$(INFO)MinIO API:     localhost:$(PUB_MINIO_API_PORT)$(RESET)"
	@echo "$(SUCCESS)========================================$(RESET)"

rework: # Перезапуск worker, нужно когда меняешь код jobs [dev]
	@echo "$(INFO)Перезапуск worker$(RESET)"
	$(DOCKER_COMPOSE) restart app
	@make wait-healthy
	@make link

connect-db: # Проверяет подключение к базе данных.
	@echo "$(INFO)Проверка подключения к базе данных$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan startup:connect:db

cron: # Синхронизирует задачи cron.
	@echo "$(INFO)Синхронизация кронов$(RESET)"
	#$(DOCKER_COMPOSE) exec app php artisan schedule-monitor:sync

psql: # Открывает консоль PostgreSQL.
	@echo "$(INFO)Подключение к PostgreSQL$(RESET)"
	$(DOCKER_COMPOSE) exec postgres psql -U $(DB_USERNAME) -d $(DB_DATABASE)

redis-cli: # Открывает консоль Redis.
	@echo "$(INFO)Подключение к Redis$(RESET)"
	$(DOCKER_COMPOSE) exec redis redis-cli

mailhog: # Открывает MailHog в браузере.
	@echo "$(INFO)Открытие MailHog$(RESET)"
	@echo "$(SUCCESS)http://localhost:$(PUB_MAILHOG_UI_PORT)$(RESET)"
	@command -v xdg-open > /dev/null && xdg-open http://localhost:$(PUB_MAILHOG_UI_PORT) || \
	command -v open > /dev/null && open http://localhost:$(PUB_MAILHOG_UI_PORT) || \
	echo "$(INFO)Откройте браузер вручную$(RESET)"

minio: # Открывает MinIO Console в браузере.
	@echo "$(INFO)Открытие MinIO Console$(RESET)"
	@echo "$(SUCCESS)http://localhost:$(PUB_MINIO_CONSOLE_PORT)$(RESET)"
	@echo "$(INFO)Логин: $(MINIO_ROOT_USER)$(RESET)"
	@echo "$(INFO)Пароль: $(MINIO_ROOT_PASSWORD)$(RESET)"
	@command -v xdg-open > /dev/null && xdg-open http://localhost:$(PUB_MINIO_CONSOLE_PORT) || \
	command -v open > /dev/null && open http://localhost:$(PUB_MINIO_CONSOLE_PORT) || \
	echo "$(INFO)Откройте браузер вручную$(RESET)"

queue: # Запускает queue worker.
	@echo "$(INFO)Запуск queue worker$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan queue:work

queue-restart: # Перезапускает queue worker.
	@echo "$(INFO)Перезапуск queue worker$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan queue:restart

tinker: # Открывает Laravel Tinker.
	@echo "$(INFO)Запуск Tinker$(RESET)"
	$(DOCKER_COMPOSE) exec app php artisan tinker

pull: # Скачивает образы контейнеров.
	@echo "$(INFO)Скачивание образов контейнеров$(RESET)"
	$(DOCKER_COMPOSE) pull

install-hooks: # Устанавливает Git hooks автоматически [dev]
	@echo "$(INFO)Установка Git hooks...$(RESET)"
	@mkdir -p .githooks
	@cp .githooks/pre-commit .git/hooks/pre-commit 2>/dev/null || echo "#!/bin/sh\n# Pre-commit hook\nvendor/bin/pint --dirty && git add -u" > .git/hooks/pre-commit
	@chmod +x .git/hooks/pre-commit
	@echo "$(SUCCESS)✅ Git hooks установлены! Pre-commit hook будет запускаться при каждом коммите$(RESET)"
	@echo "$(INFO)Для пропуска хука используйте: git commit --no-verify$(RESET)"

uninstall-hooks: # Удаляет Git hooks [dev]
	@echo "$(INFO)Удаление Git hooks...$(RESET)"
	@rm -f .git/hooks/pre-commit
	@echo "$(SUCCESS)✅ Git hooks удалены$(RESET)"

# Pint команды
pint: # Запускает Pint для всего проекта [dev]
	@echo "$(INFO)Запуск Pint для всего проекта$(RESET)"
	$(DOCKER_COMPOSE) exec app vendor/bin/pint

pint-dirty: # Запускает Pint только для измененных файлов [dev]
	@echo "$(INFO)Запуск Pint для измененных файлов$(RESET)"
	$(DOCKER_COMPOSE) exec app vendor/bin/pint --dirty

pint-test: # Проверяет стиль кода без внесения изменений [dev]
	@echo "$(INFO)Проверка Pint стиля кода$(RESET)"
	$(DOCKER_COMPOSE) exec app vendor/bin/pint --test

pint-path: # Запускает Pint для указанного пути (make pint-path p=app/Models) [dev]
	@if [ -z "$(p)" ]; then \
		echo "$(INFO)Укажите путь: make pint-path p=путь/к/файлу$(RESET)"; \
		exit 1; \
	fi
	@echo "$(INFO)Запуск Pint для $(p)$(RESET)"
	$(DOCKER_COMPOSE) exec app vendor/bin/pint $(p)

serve: # hot reload page
	@echo "$(INFO)bun serve$(RESET)"
	$(DOCKER_COMPOSE) exec app bun run dev
