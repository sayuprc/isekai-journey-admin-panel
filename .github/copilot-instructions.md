# Project Guidelines

## Project Overview

`isekai-observatory` はモノレポです。

- `src/server`: PHP 8.5 / Laravel API
- `src/admin`: Astro / SolidJS / Elysia による管理画面
- `src/viewer`: Astro / SolidJS による閲覧サイト
- `src/contracts`: TypeSpec で管理する API コントラクト

タスク開始時は次の短い入口を優先してください。

- `README.md`: セットアップと主要コマンド
- `ARCHITECTURE.md`: 責務分割と変更ルート
- `FRONTEND.md`: 管理画面 / 閲覧サイトの UI 実装方針
- `PLANS.md`: 実行計画を書く基準と運用方法
- `docs/INDEX.md`: リポジトリ内ドキュメントの索引
- `docs/adr/INDEX.md`: 採用済みアーキテクチャ判断

## Workflow

- まず変更対象のサブプロジェクトを 1 つに絞って作業する
- 仕様が曖昧な非自明な変更は `docs/product-specs/` に先に整理する
- 複数ステップの変更は `docs/exec-plans/active/` に計画を残してから進める
- 繰り返し参照される判断や運用ルールは、会話ではなくリポジトリ内ドキュメントに残す
- 既存の ADR や `docs/` と矛盾する変更を行う場合は、コードだけでなく関連文書も更新する

## Build And Test

- 開発環境の起動と共通タスクは `mise` を使う
- `src/` 配下の TypeScript プロジェクトは `bun workspace` で管理する
- 各 Bun package の script は `src/` で `bun --filter <package> <script>` として実行する
- PHP 関連のコマンドは Docker コンテナ経由で実行する
- 変更後は `mise tasks` で関連タスクを確認し、変更箇所に最も近い検証を優先する

## Conventions

- リンター・フォーマッター設定を変えてエラーを回避しない
- 生成物は手動編集しない
  - `src/server/Generated/`
  - `src/admin/src/generated/`
  - `src/viewer/src/generated/`
  - `src/contracts/generated/`
- `npm` / `yarn` / `pnpm` は使わず、`bun` を使う
- `git commit --no-verify` は使わない
