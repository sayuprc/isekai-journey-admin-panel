# ヰ世界観測所

ヰ世界情緒の情報を管理するためのモノレポです。

## 構成

- アプリケーション本体: `src/`
- 開発環境と共通タスク: `docker/`, `compose.yaml`, `mise.toml`
- 詳細: `ARCHITECTURE.md`

## セットアップ

前提:

- Docker / Docker Compose
- `mise`

最初のセットアップ:

1. `mise install`
2. `mise run setup`
3. 必要な追加タスクは `mise tasks` で確認する

worktree ごとに Docker 環境を並列起動したい場合は、各 worktree のリポジトリ直下に未追跡の `.env` を置き、`COMPOSE_PROJECT_NAME` と公開ポートをずらす。

```dotenv
COMPOSE_PROJECT_NAME=isekai-observatory-feature-a
PROXY_HTTP_PORT=18080
PROXY_HTTPS_PORT=18443
PHP_HTTP_PORT=28080
MYSQL_PORT=13306
REDIS_PORT=16379
REDIS_HTTP_PORT=18079
ADMIN_PORT=14321
VIEWER_PORT=13000
```

`php` / `mysql` / `redis` は Compose の service 名で相互接続され、`proxy` は `ADMIN_PORT` / `VIEWER_PORT` でホスト上の Astro dev server を見にいく。worktree ごとに project 名と公開ポートを分ければコンテナ同士が干渉しない。

admin / viewer を worktree ごとに起動するときは、同じ `.env` を見たうえで次のように実行する。

- `cd src && bun --filter admin dev`
- `cd src && bun --filter viewer dev`

TypeScript 関連: `src/` を Bun workspace のルートとして扱い、各 package script は `cd src && bun --filter <package> <script>` で実行する

## ドキュメント

- `AGENTS.md`
- `ARCHITECTURE.md`
- `docs/INDEX.md`
