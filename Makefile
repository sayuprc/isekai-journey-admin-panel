SHELL := /bin/bash

.DEFAULT_GOAL := help

UID := $(shell id -u)
USERNAME := $(shell id -u -n)
GID := $(shell id -g)
GROUPNAME := $(shell id -g -n)

SERVER_CONTAINER := isekai-journey-admin-php
CLIENT_CONTAINER := isekai-journey-admin-node

PROTOC_VERSION := "27.3"
GRPC_VERSION := "v1.65.5"

.PHONY: build
build: ## Build docker image for develop environment
	docker build -t isekai-journey-admin-web:1.25 ./docker/nginx
	docker build -t isekai-journey-admin-php:8.4 ./docker/php \
		--build-arg UID=${UID} \
		--build-arg GID=${GID} \
		--build-arg USERNAME=${USERNAME} \
		--build-arg GROUPNAME=${GROUPNAME} \
		--build-arg PROTOC_VERSION=${PROTOC_VERSION} \
		--build-arg GRPC_VERSION=${GRPC_VERSION}
	docker build -t isekai-journey-admin-node:22 ./docker/node
	docker build -t isekai-journey-admin-db:16 ./docker/postgresql

.PHONY: up
up: ## Start the container
	docker compose up -d

.PHONY: down
down: ## Delete the container
	docker compose down

.PHONY: php
php: ## Enter php container
	docker exec -it ${SERVER_CONTAINER} bash

.PHONY: composer-install
composer-install: ## Install composer packages
	docker compose run --rm php composer install

.PHONY: phpstan
phpstan: ## Run PHPStan
	docker exec ${SERVER_CONTAINER} composer phpstan

.PHONY: phpstan-clear-cache
phpstan-clear-cache: ## Clear PHPStan cache
	docker exec ${SERVER_CONTAINER} composer phpstan-clear-cache

.PHONY: arkitect
arkitect: ## Run arkitect
	docker exec ${SERVER_CONTAINER} composer arkitect

.PHONY: ecs
ecs: ## Run ecs
	docker exec ${SERVER_CONTAINER} composer ecs

.PHONY: ecs-fix
ecs-fix: ## Run ecs fix
	docker exec ${SERVER_CONTAINER} composer ecs-fix

.PHONY: test-all
test-all: ## Run all tests
	docker exec ${SERVER_CONTAINER} composer test-all

.PHONY: test-unit
test-unit: ## Run PHPUnit
	docker exec ${SERVER_CONTAINER} composer test-unit

.PHONY: test-feature
test-feature: ## Run PHPUnit
	docker exec ${SERVER_CONTAINER} composer test-feature

.PHONY: coverage
coverage: ## Export coverage
	docker exec ${SERVER_CONTAINER} composer coverage

.PHONY: infection
infection: ## Run infection
	docker exec ${SERVER_CONTAINER} composer infection

.PHONY: ide-gen
ide-gen: ## Generate ide helper file
	docker exec ${SERVER_CONTAINER} composer ide-gen

.PHONY: ide-model
ide-model: ## Write ide helper to model files
	docker exec ${SERVER_CONTAINER} composer ide-model

.PHONY: ide-meta
ide-meta: ## Generate ide helper meta file
	docker exec ${SERVER_CONTAINER} composer ide-meta

.PHONY: migrate
migrate: ## Migrate database
	docker exec ${SERVER_CONTAINER} php artisan migrate

.PHONY: migrate-test
migrate-test: ## Migrate database for test db
	docker exec ${SERVER_CONTAINER} php artisan migrate --env=testing

.PHONY: tinker
tinker: ## Run tinker
	docker exec ${SERVER_CONTAINER} php artisan tinker

.PHONY: copy-root-ca
copy-root-ca: ## Copy local rootCA.pem
	cp $$(mkcert -CAROOT)/rootCA.pem docker/php/certs/
	cp $$(mkcert -CAROOT)/rootCA.pem docker/node/certs/

.PHONY: generate-grpc-stub
generate-grpc-stub: ## Generate gRPC Stub files
	docker exec ${SERVER_CONTAINER} ./gen-stub.sh

.PHONY: node
node: ## Enter node container
	docker exec -it ${CLIENT_CONTAINER} bash

.PHONY: npm-install
npm-install: ## Install npm packages
	docker compose run --rm node npm i

.PHONY: format
format: ## Run ESLint with --fix and stylelint with --fix
	make lint-fix
	make style-fix

.PHONY: lint
lint: ## Run ESLint
	docker exec ${CLIENT_CONTAINER} npm run lint

.PHONY: lint-fix
lint-fix: ## Run ESLint with --fix
	docker exec ${CLIENT_CONTAINER} npm run lint:fix

.PHONY: style
style: ## Run stylelint
	docker exec ${CLIENT_CONTAINER} npm run style

.PHONY: style-fix
style-fix: ## Run stylelint with --fix
	docker exec ${CLIENT_CONTAINER} npm run style:fix

.PHONY: tcm
tcm: ## Run tcm
	docker exec ${CLIENT_CONTAINER} npm run tcm

.PHONY: tcm-watch
tcm-watch: ## Run tcm with --watch
	docker exec ${CLIENT_CONTAINER} npm run tcm:watch

.PHONY: help
help: ## Display a list of targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
