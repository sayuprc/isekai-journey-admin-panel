SHELL := /bin/bash

.DEFAULT_GOAL := help

.PHONY: build
build: ## Build docker image
	docker build -t isekai-terrarium:22 ./docker/node

.PHONY: up
up: ## Start the container
	docker compose up -d

.PHONY: down
down: ## Delete the container
	docker compose down

.PHONY: node
node: ## Enter node container
	docker compose exec node bash

.PHONY: npm-install
npm-install: ## Install packages
	docker compose run --rm node npm i

.PHONY: copy-root-ca
copy-root-ca: ## Copy local rootCA.pem
	cp $$(mkcert -CAROOT)/rootCA.pem docker/node/certs/

.PHONY: lint
lint: ## Run ESLint
	docker compose exec node npm run lint

.PHONY: lint-fix
lint-fix: ## Run ESLint with --fix
	docker compose exec node npm run lint:fix

.PHONY: style
style: ## Run stylelint
	docker compose exec node npm run style

.PHONY: style-fix
style-fix: ## Run stylelint with --fix
	docker compose exec node npm run style:fix

.PHONY: tcm
tcm: ## Run tcm
	docker compose exec node npm run tcm

.PHONY: tcm-watch
tcm-watch: ## Run tcm with --watch
	docker compose exec node npm run tcm:watch

.PHONY: help
help: ## Display a list of targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
