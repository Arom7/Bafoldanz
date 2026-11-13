# Atajos oficiales del proyecto — docs/05-despliegue.md
# Nunca PHP/Composer en el host: todo dentro de contenedores.

COMPOSE := docker compose

# Defaults que coinciden con .env.example (usados por test-db)
DB_USERNAME ?= bafoldanz
DB_ROOT_PASSWORD ?= root-secret

.DEFAULT_GOAL := help
.PHONY: help setup up down shell logs migrate seed test test-db pint pint-test routes key

help: ## Lista los comandos disponibles
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

setup: ## Primer arranque: copia .env, build, composer install, key, migrate --seed
	@if [ ! -f .env ]; then cp .env.example .env; echo ">> .env creado desde .env.example"; fi
	$(COMPOSE) build --build-arg UID=1000 --build-arg GID=1000
	$(COMPOSE) up -d
	$(COMPOSE) exec -T app composer install --no-interaction
	$(COMPOSE) exec -T app php artisan key:generate --force
	$(COMPOSE) exec -T app php artisan migrate --seed
	@echo ">> Listo: http://localhost:8000"

up: ## Levanta el stack local
	$(COMPOSE) up -d

down: ## Baja el stack local
	$(COMPOSE) down

shell: ## Shell dentro del contenedor app (Alpine usa sh)
	$(COMPOSE) exec app sh

logs: ## Logs del contenedor app
	$(COMPOSE) logs -f app

migrate: ## php artisan migrate (nunca fresh)
	$(COMPOSE) exec app php artisan migrate

seed: ## Ejecuta los seeders
	$(COMPOSE) exec app php artisan db:seed

test-db: ## Crea la BD de tests bafoldanz_test si no existe (idempotente)
	$(COMPOSE) exec -T -e DBU="$(DB_USERNAME)" -e DBR="$(DB_ROOT_PASSWORD)" app php -r 'try { $$p = new PDO("mysql:host=mysql", "root", getenv("DBR")); $$p->exec("CREATE DATABASE IF NOT EXISTS bafoldanz_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); $$p->exec("GRANT ALL PRIVILEGES ON bafoldanz_test.* TO " . $$p->quote(getenv("DBU")) . "@\x27%\x27"); echo "bafoldanz_test lista\n"; } catch (Exception $$e) { fwrite(STDERR, $$e->getMessage()."\n"); exit(1); }'

test: ## PHPUnit contra la BD de tests (Fase 1 configura phpunit.xml)
	$(COMPOSE) exec app php artisan test

pint: ## Corrige estilo PSR-12 con Pint
	$(COMPOSE) exec app ./vendor/bin/pint

pint-test: ## Verifica estilo PSR-12 sin corregir
	$(COMPOSE) exec app ./vendor/bin/pint --test

routes: ## Lista de rutas
	$(COMPOSE) exec app php artisan route:list

key: ## Genera APP_KEY
	$(COMPOSE) exec app php artisan key:generate --force
