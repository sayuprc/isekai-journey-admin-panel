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

## Source Files

- `compose.yaml`
- `docker/`
- `mise.toml`

起動構成や URL を変えるときは、近接する入口文書も同じ変更で更新します。
