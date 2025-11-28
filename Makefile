SHELL := /bin/bash

.DEFAULT_GOAL := help

UID := $(shell id -u)
USERNAME := $(shell id -u -n)
GID := $(shell id -g)
GROUPNAME := $(shell id -g -n)

SERVER_CONTAINER := isekai-terrarium-admin-php
CLIENT_CONTAINER := isekai-terrarium-admin-node

.PHONY: build
build: ## 開発環境用の Docker イメージをビルド
	docker build -t isekai-terrarium-proxy:1.27 ./docker/nginx
	docker build -t isekai-terrarium-admin-php:8.4 ./docker/php \
		--build-arg UID=${UID} \
		--build-arg GID=${GID} \
		--build-arg USERNAME=${USERNAME} \
		--build-arg GROUPNAME=${GROUPNAME}
	docker build -t openapi-generator:latest ./docker/openapi-generator \
		--build-arg UID=${UID} \
		--build-arg GID=${GID} \
		--build-arg USERNAME=${USERNAME} \
		--build-arg GROUPNAME=${GROUPNAME}
	docker build -t isekai-terrarium-admin-node:22 ./docker/node

.PHONY: up
up: ## コンテナを起動
	docker compose up -d

.PHONY: down
down: ## コンテナを削除
	docker compose down

.PHONY: php
php: ## PHP コンテナに入る
	docker exec -it ${SERVER_CONTAINER} bash

.PHONY: composer-install
composer-install: ## Composer パッケージをインストール
	docker compose run --rm php composer install

.PHONY: phpstan
phpstan: ## PHPStan を実行
	docker exec ${SERVER_CONTAINER} composer phpstan

.PHONY: phpstan-clear-cache
phpstan-clear-cache: ## PHPStan のキャッシュをクリア
	docker exec ${SERVER_CONTAINER} composer phpstan-clear-cache

.PHONY: arkitect
arkitect: ## arkitect を実行
	docker exec ${SERVER_CONTAINER} composer arkitect

.PHONY: ecs
ecs: ## ECS を実行
	docker exec ${SERVER_CONTAINER} composer ecs

.PHONY: ecs-fix
ecs-fix: ## ECS を実行して修正
	docker exec ${SERVER_CONTAINER} composer ecs-fix

.PHONY: test-all
test-all: ## すべてのテストを実行
	docker exec ${SERVER_CONTAINER} composer test-all

.PHONY: test-unit
test-unit: ## ユニットテストを実行
	docker exec ${SERVER_CONTAINER} composer test-unit

.PHONY: test-integration
test-integration: ## 統合テストを実行
	docker exec ${SERVER_CONTAINER} composer test-integration

.PHONY: test-feature
test-feature: ## フィーチャーテストを実行
	docker exec ${SERVER_CONTAINER} composer test-feature

.PHONY: coverage
coverage: ## カバレッジをエクスポート
	docker exec ${SERVER_CONTAINER} composer coverage

.PHONY: infection
infection: ## infection を実行
	docker exec ${SERVER_CONTAINER} composer infection

.PHONY: metrics
metrics: ## メトリクスを実行
	docker exec ${SERVER_CONTAINER} composer metrics

.PHONY: migrate
migrate: ## データベースのマイグレーション
	@docker exec ${SERVER_CONTAINER} bash -c "cd database/atlas; ./apply.sh"

.PHONY: tinker
tinker: ## tinker を実行
	docker exec ${SERVER_CONTAINER} php artisan tinker

.PHONY: openapi-generate
openapi-generate: ## OpenAPI からコードを生成
	rm -rf ./src/server/Generated
	docker run --rm -u ${UID}:${GID} -v ".:/local" openapi-generator generate \
		-i /local/src/contracts/generated/oas/openapi.yaml \
		-g php \
		-t /local/src/server/tools/openapi-generator/templates \
		-o /local/src/server/Generated
	rm -rf ./src/client/src/generated
	docker exec ${CLIENT_CONTAINER} npm run generate:api

.PHONY: mkcert
mkcert: ## 証明書を作成
	mkcert \
		--key-file docker/nginx/certs/server.key \
		--cert-file docker/nginx/certs/server.crt \
		localhost \
		127.0.0.1 \
		local.admin.terrarium.isekaijoucho.fan \
		local.api.terrarium.isekaijoucho.fan \
		local.terrarium.isekaijoucho.fan

.PHONY: copy-root-ca
copy-root-ca: ## ローカルの rootCA.pem をコピー
	cp $$(mkcert -CAROOT)/rootCA.pem docker/php/certs/
	cp $$(mkcert -CAROOT)/rootCA.pem docker/node/certs/

.PHONY: node
node: ## Node コンテナに入る
	docker exec -it ${CLIENT_CONTAINER} bash

.PHONY: npm-install
npm-install: ## npm パッケージをインストール
	docker compose run --rm node npm i

.PHONY: format
format: ## ESLint と stylelint を --fix オプションで実行
	make lint-fix
	make style-fix

.PHONY: lint
lint: ## ESLint を実行
	docker exec ${CLIENT_CONTAINER} npm run lint

.PHONY: lint-fix
lint-fix: ## ESLint を --fix オプションで実行
	docker exec ${CLIENT_CONTAINER} npm run lint:fix

.PHONY: style
style: ## stylelint を実行
	docker exec ${CLIENT_CONTAINER} npm run style

.PHONY: style-fix
style-fix: ## stylelint を --fix オプションで実行
	docker exec ${CLIENT_CONTAINER} npm run style:fix

.PHONY: tcm
tcm: ## tcm を実行
	docker exec ${CLIENT_CONTAINER} npm run tcm

.PHONY: tcm-watch
tcm-watch: ## tcm を --watch オプションで実行
	docker exec ${CLIENT_CONTAINER} npm run tcm:watch

.PHONY: prism
prism: ## prism モックを実行
	docker exec -it ${CLIENT_CONTAINER} npm run prism

.PHONY: help
help: ## ターゲットの一覧を表示
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
