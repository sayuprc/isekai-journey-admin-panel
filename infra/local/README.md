# Local Infrastructure

ローカル開発用のコンテナ定義です

サービス構成、worktree 分離、並列運用の詳細は `docs/design-docs/local-runtime-topology.md` を参照する

## 置き場所

| パス | 内容 |
|---|---|
| `docker/` | Compose から参照する proxy / php / mysql / openapi-generator などの定義 |
| `docker/nginx/certs` | TLS 証明書 |
| `docker/php/certs/rootCA.pem` | PHP 側で参照する root CA |

リポジトリ直下の `compose.yaml` と `mise.toml` が起動の入口です
