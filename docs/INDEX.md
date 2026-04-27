# Docs Index

このディレクトリは、リポジトリ内で共有すべき知識を置くための記録システムです。

入口文書は短く保ち、詳細は下位の文書へ分けます。

## Maps

- `agent-map.md`: エージェント向けの共通地図
- `../ARCHITECTURE.md`: リポジトリ全体の地図
- `../FRONTEND.md`: 管理画面と閲覧サイトの UI 方針
- `../PLANS.md`: 実行計画の運用ルール
- `adr/INDEX.md`: 採用済み ADR の一覧
- `design-docs/INDEX.md`: 継続的な設計原則の一覧

## Key Design Docs

- `design-docs/subproject-boundaries.md`: `contracts` / `server` / `admin` / `viewer` の責務境界
- `design-docs/local-runtime-topology.md`: ローカル開発時のサービス構成、worktree 分離、並列実装の運用

## Working Records

- `product-specs/INDEX.md`: 機能や施策の仕様メモ
- `references/INDEX.md`: 外部資料や参考実装の要点
- `exec-plans/README.md`: 実行計画の運用方法
- `tech-debt-tracker.md`: 継続的に追う技術的負債

## Placement Guide

- 受け入れ済みの構造判断: `docs/adr/`
- 継続的に効く設計原則や信条: `docs/design-docs/`
- 機能、施策、画面変更の仕様: `docs/product-specs/`
- 外部記事、他 repo、仕様書の要約: `docs/references/`
- 複数ステップにまたがる実行計画: `docs/exec-plans/active/`
- 完了した実行計画の履歴: `docs/exec-plans/completed/`
- 繰り返し手当てが必要な負債: `docs/tech-debt-tracker.md`

## Update Rule

コード変更が既存文書を古くした場合は、同じ変更で最も近い文書も更新します。
