# Architecture

## Overview

`isekai-observatory` は、API サーバー、API コントラクト、管理画面、閲覧サイトを 1 つのモノレポで管理します。

このリポジトリでは、API 契約は `src/contracts` の TypeSpec を起点にし、生成物を各実装へ配布します。コード変更時は「どの層が Source of Truth か」を先に見極めることが重要です。

## Top-Level Map

- `docker/`: ローカル開発用コンテナ定義と TLS 証明書設定
- `docs/`: ADR、設計原則、実行計画、技術的負債の記録
- `src/`: `admin` / `viewer` / `contracts` を束ねる Bun workspace のルート
- `src/server/`: PHP 8.5 / Laravel API サーバー
- `src/contracts/`: TypeSpec による API 契約
- `src/admin/`: Astro / SolidJS / Elysia による管理画面
- `src/viewer/`: Astro / SolidJS による閲覧サイト
- `mise.toml`: 開発環境の標準タスクとツール定義
- `compose.yaml`: ローカルで使う proxy / php / mysql / redis の定義

## Runtime Topology

- `proxy`: Nginx がローカル TLS とホスト名を受け持つ
- `php`: Laravel API を実行する
- `mysql`: 開発用データベース
- `redis` / `redis-http`: Redis と HTTP 越しの接続口

ローカルの代表 URL:

- `https://local.api.isekaijoucho.fan`
- `https://local.admin.isekaijoucho.fan`
- `https://local.isekaijoucho.fan`

## Source-Of-Truth Flow

1. API 契約は `src/contracts/src/admin/main.tsp` と `src/contracts/src/viewer/main.tsp` から始まる
2. TypeSpec から `src/contracts/generated/oas/` に OpenAPI を生成する
3. OpenAPI から次の生成物を更新する
  - `src/server/Generated/`
  - `src/admin/src/generated/`
  - `src/viewer/src/generated/`
4. サーバー実装とフロントエンドは生成済みの契約を前提に振る舞いを実装する

契約を変える変更は、生成物ではなく `src/contracts` を最初に編集する。

## Server

サーバーは Laravel を外側の実行基盤として使い、業務ロジックは `packages/` 配下のドメインごとに分ける。

- `app/`: Laravel 固有の wiring
- `packages/{Package}/`: 業務ドメイン
- `database/atlas/`: Atlas によるスキーマ管理
- `tests/`: Unit / Integration / Feature テスト
- `Generated/`: OpenAPI 由来の生成コード

採用アーキテクチャは ADR-0006 の ADOP。実務上は次の境界を守る。

- `Domain`: ビジネスルール
- `Application`: ユースケース
- `Infrastructures`: 永続化や外部接続
- `DebugInfrastructures`: テスト用実装

## Admin

管理画面は Astro をページの骨格に使い、対話的な UI は SolidJS に分離する。`src/server/` には管理画面専用の BFF を置く。

- `src/pages/`: Astro ページ
- `src/layouts/`: レイアウト
- `src/components/`: SolidJS コンポーネント
- `src/server/`: Elysia ベースの BFF / サーバー処理
- `src/schemas/`: 入出力スキーマ
- `src/generated/`: OpenAPI 由来の生成クライアント

## Viewer

閲覧サイトも Astro / SolidJS 構成だが、管理画面より単純な表示系の責務が中心。

- `src/pages/`: ページ
- `src/layouts/`: レイアウト
- `src/components/`: UI コンポーネント
- `src/schemas/`: フロントエンド側のスキーマ
- `src/styles/`: スタイル

## Change Routing

| 変更したいもの | 最初に触る場所 | 続けて触る場所 | 代表的な確認 |
|---|---|---|---|
| API の request / response 形状 | `src/contracts` | `mise run generate` の影響先 | `cd src && bun --filter contracts format:check`, `test`, `compile:*` |
| サーバーの業務ロジック | `src/server` | 必要なら `src/contracts` | `mise run ecs`, `phpstan`, `arkitect`, `test` |
| 管理画面の UI / BFF | `src/admin` | 必要なら `src/contracts` | `cd src && bun --filter admin lint:check`, `style:check`, `build` |
| 閲覧サイトの UI | `src/viewer` | 必要なら `src/contracts` | `cd src && bun --filter viewer lint:check`, `style:check`, `build` |
| 開発環境 | `mise.toml`, `compose.yaml`, `docker/` | 関連 docs | 起動確認と影響範囲の明記 |

## Validation Strategy

- まず変更箇所に最も近い検証を回す
- 共通タスクは `mise tasks` から見つける
- 生成物は手動で直さず、生成元を修正して再生成する
- 繰り返し迷う判断は ADR または `docs/design-docs/` に昇格させる

## Related Documents

- `README.md`
- `FRONTEND.md`
- `PLANS.md`
- `docs/INDEX.md`
- `docs/adr/INDEX.md`
- `docs/design-docs/core-beliefs.md`
