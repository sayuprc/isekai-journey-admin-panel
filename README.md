# ヰ世界観測所

ヰ世界情緒の情報を管理するためのモノレポです。

[![coverage](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/sayuprc/isekai-observatory/metrics/docs/metrics/badges/coverage.json)](docs/metrics/README.md)
[![mutation score](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/sayuprc/isekai-observatory/metrics/docs/metrics/badges/msi.json)](docs/metrics/README.md)
[![covered MSI](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/sayuprc/isekai-observatory/metrics/docs/metrics/badges/covered-msi.json)](docs/metrics/README.md)
[![maintainability](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/sayuprc/isekai-observatory/metrics/docs/metrics/badges/maintainability.json)](docs/metrics/README.md)
[![avg CCN](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/sayuprc/isekai-observatory/metrics/docs/metrics/badges/complexity.json)](docs/metrics/README.md)

Server のカバレッジ / Infection / PhpMetrics は週次 CI で更新されます。詳細は `docs/metrics/README.md`。

## 構成

- アプリケーション本体: `src/`
- ローカル開発環境: `compose.yaml`, `infra/local/docker/`, `mise.toml`
- 環境別インフラ定義: `infra/staging/`, `infra/production/`
- 詳細: `ARCHITECTURE.md`

## セットアップ

前提:

- Docker / Docker Compose
- `mise`

最初のセットアップ:

1. `mise install`
2. `mise run setup`
3. 必要な追加タスクは `mise tasks` で確認する

`git worktree` を使うローカル開発運用は `docs/design-docs/local-runtime-topology.md` を参照する。

TypeScript 関連: パッケージ管理は `src/` の pnpm workspace で行い、依存関係は `mise run pnpm:install` でインストールする。各 package script は Bun 実行環境で `cd src && bun --filter <package> <script>` として実行する。

## ドキュメント

- `docs/agent-map.md`
- `ARCHITECTURE.md`
- `docs/INDEX.md`
