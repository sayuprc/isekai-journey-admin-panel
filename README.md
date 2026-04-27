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

`git worktree` を使うローカル開発運用は `docs/design-docs/local-runtime-topology.md` を参照する。

TypeScript 関連: `src/` を Bun workspace のルートとして扱い、各 package script は `cd src && bun --filter <package> <script>` で実行する

## ドキュメント

- `docs/agent-map.md`
- `ARCHITECTURE.md`
- `docs/INDEX.md`
