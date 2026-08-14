# Architecture

## Overview

`isekai-observatory` は、API サーバー、API コントラクト、管理画面、閲覧サイトを 1 つのモノレポで管理します

このリポジトリでは、API 契約は `src/contracts` の TypeSpec を起点にし、生成物を各実装へ配布します。コード変更時は「どの層が Source of Truth か」を先に見極めることが重要です。この文書は上位マップにとどめ、ローカル実行構成や各サブプロジェクトの中身は下位文書へ分けます

## Top-Level Map

- `infra/local/docker/`: ローカル開発用コンテナ定義と TLS 証明書設定
- `infra/development/`: 開発環境向け Cloud Build 定義とアプリケーション用 Dockerfile
- `infra/staging/`: ステージング向け Cloud Build 定義とアプリケーション用 Dockerfile
- `infra/production/`: 本番向け Cloud Build 定義とアプリケーション用 Dockerfile
- `docs/`: ADR、設計原則、仕様、参照メモ、実行計画、運用手順、技術的負債の記録
- `src/`: `admin` / `viewer` / `contracts` を束ねる pnpm workspace のルート
- `src/server/`: PHP 8.5 / Laravel API サーバー
- `src/contracts/`: TypeSpec による API 契約
- `src/admin/`: Astro / SolidJS / Elysia による管理画面
- `src/viewer/`: Astro / SolidJS による閲覧サイト
- `src/discord-notifier/`: MoonBit 製 Discord 通知配達サービス (Cloud Run)
- `src/notify-publish/`: MoonBit 製の通知 JSON → Pub/Sub publish CLI
- `src/notify-contract/`: MoonBit 製のアプリ通知 JSON 共有契約 (`notify-publish` / `discord-notifier` が依存)
- `mise.toml`: 開発ツール・タスク定義、および CI / インフラ向け版ピンのカタログ(`[tools]` / `[vars]`)
- `compose.yaml`: ローカルで使う proxy / php / mysql / redis の定義

## Source-Of-Truth Flow

1. API 契約は `src/contracts/src/admin/main.tsp` と `src/contracts/src/viewer/main.tsp` から始まる
2. TypeSpec から `src/contracts/generated/oas/` に OpenAPI を生成する
3. OpenAPI から次の生成物を更新する
  - `src/server/Generated/`
  - `src/admin/src/generated/`
  - `src/viewer/src/generated/`
4. サーバー実装とフロントエンドは生成済みの契約を前提に振る舞いを実装する

契約を変える変更は、生成物ではなく `src/contracts` を最初に編集する

## Change Routing

変更後の確認は、まず表の最右列にある最も近い検証を起点にします

| 変更したいもの | 最初に触る場所 | 続けて触る場所 | 代表的な確認 |
|---|---|---|---|
| API の request / response 形状 | `src/contracts` | `mise run generate` の影響先 | `mise run contract:format:check`, `contract:test`, `contract:compile:*` |
| サーバーの業務ロジック | `src/server` | 必要なら `src/contracts` | `mise run ecs`, `phpstan`, `arkitect`, `test` |
| 管理画面の UI / BFF | `src/admin` | 必要なら `src/contracts` | `cd src && bun --filter admin lint:check`, `style:check`, `build` |
| 閲覧サイトの UI | `src/viewer` | 必要なら `src/contracts` | `cd src && bun --filter viewer lint:check`, `style:check`, `build` |
| Discord 通知配達 | `src/discord-notifier` | 各 env の `docker/discord-notifier`、Cloud Build | `mise run discord-notifier:check` |
| 通知 Pub/Sub publish | `src/notify-publish` | 各 env の `docker/viewer` (viewer-deploy から利用) | `mise run notify-publish:check` |
| アプリ通知 JSON 契約 | `src/notify-contract` | `notify-publish` / `discord-notifier` | `mise run notify-contract:check` |
| 開発環境 | `mise.toml`, `compose.yaml`, `infra/local/` | `docs/design-docs/local-runtime-topology.md` | 起動確認と影響範囲の明記 |
| 環境別インフラ | `infra/development/`, `infra/staging/`, `infra/production/` | `infra/README.md` | Cloud Build 設定、Dockerfile、build context の確認 |

## Detail Documents

| 文書 | 内容 |
|---|---|
| `README.md` | セットアップと主要コマンド |
| `FRONTEND.md` | 管理画面と閲覧サイトの UI 方針 |
| `PLANS.md` | 実行計画を書くタイミング |
| `docs/agent-map.md` | エージェント向けの共通地図 |
| `docs/INDEX.md` | 文書の置き場所と一覧 |
| `docs/adr/INDEX.md` | 採用済み ADR |
| `docs/design-docs/INDEX.md` | 継続的な設計原則 |
| `docs/design-docs/core-beliefs.md` | 開発原則 |
| `docs/design-docs/local-runtime-topology.md` | ローカル実行構成と worktree 運用 |
| `docs/design-docs/subproject-boundaries.md` | サブプロジェクトの責務境界 |
| `docs/product-specs/INDEX.md` | 機能・施策の仕様 |
| `docs/references/INDEX.md` | 外部資料の要約 |
| `docs/operations/INDEX.md` | 運用手順 |
| `infra/README.md` | ローカルと環境別インフラ |
| `docs/tech-debt-tracker.md` | 技術的負債 |
