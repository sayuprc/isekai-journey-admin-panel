# Local Runtime Topology

ローカル開発時の実行構成をまとめる文書です。

## Services

- `proxy`: Nginx がローカル TLS とホスト名を受け持つ
- `php`: Laravel API を実行する
- `mysql`: 開発用データベース
- `redis` / `redis-http`: Redis と HTTP 越しの接続口

## Local URLs

- `https://local.api.isekaijoucho.fan`
- `https://local.admin.isekaijoucho.fan`
- `https://local.isekaijoucho.fan`

## Worktree Isolation

- Compose の project 名は worktree ごとに `COMPOSE_PROJECT_NAME` で分ける
- ホストへ公開するポートは `PROXY_HTTP_PORT`, `PROXY_HTTPS_PORT`, `PHP_HTTP_PORT`, `MYSQL_PORT`, `REDIS_PORT`, `REDIS_HTTP_PORT` で worktree ごとにずらす
- `proxy` から `php` への API 接続は Compose ネットワーク内の service 名 `php` を使うため、`php` のホスト公開ポートを共有しない
- `mysql_data` などの named volume は Compose project ごとに分離される

## Source Files

- `compose.yaml`
- `docker/`
- `mise.toml`

起動構成や URL を変えるときは、近接する入口文書も同じ変更で更新します。
