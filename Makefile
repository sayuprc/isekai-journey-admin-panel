SHELL := /bin/bash

.DEFAULT_GOAL := help

UID := $(shell id -u)
USERNAME := $(shell id -u -n)
GID := $(shell id -g)
GROUPNAME := $(shell id -g -n)

.PHONY: build
build: ## Build docker image for develop environment
	docker build -t isekai-journey-admin-web:1.25 ./docker/nginx
	docker build -t isekai-journey-admin-php:8.3 ./docker/php \
		--build-arg UID=${UID} \
		--build-arg GID=${GID} \
		--build-arg USERNAME=${USERNAME} \
		--build-arg GROUPNAME=${GROUPNAME}
	docker build -t isekai-journey-admin-db:16 ./docker/postgresql

.PHONY: up
up: ## Start the container
	docker compose up -d

.PHONY: down
down: ## Delete the container
	docker compose down

.PHONY: php
php: ## Enter php container
	docker compose exec php bash

.PHONY: composer-install
composer-install: ## Install composer packages
	docker compose exec php composer install

.PHONY: stan
stan: ## Run PHPStan
	docker compose exec php composer stan

.PHONY: ecs
ecs: ## Run ecs
	docker compose exec php composer ecs

.PHONY: ecs-fix
ecs-fix: ## Run ecs fix
	docker compose exec php composer ecs-fix

.PHONY: test-unit
test-unit: ## Run PHPUnit
	docker compose exec php composer test-unit

.PHONY: ide-gen
ide-gen: ## Generate ide helper file
	docker compose exec php composer ide-gen

.PHONY: ide-model
ide-model: ## Write ide helper to model files
	docker compose exec php composer ide-model

.PHONY: ide-meta
ide-meta: ## Generate ide helper meta file
	docker compose exec php composer ide-meta

.PHONY: migrate
migrate: ## Migrate database
	docker compose exec php php artisan migrate

.PHONY: migrate-test
migrate-test: ## Migrate database for test db
	docker compose exec php php artisan migrate --env=testing

.PHONY: tinker
tinker: ## Run tinker
	docker compose exec php php artisan tinker

.PHONY: npm-install
npm-install: ## Install npm packages
	docker compose exec php npm i

.PHONY: npm-dev
npm-dev: ## Run npm run dev
	docker compose exec php npm run dev --host

.PHONY: eslint
eslint: ## Run npm run eslint
	docker compose exec php npm run eslint

.PHONY: eslint-fix
eslint-fix: ## Run npm run lint:fix
	docker compose exec php npm run eslint:fix

.PHONY: help
help: ## Display a list of targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
