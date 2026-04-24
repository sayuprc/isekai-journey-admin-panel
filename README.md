# ヰ世界観測所

ヰ世界情緒の情報を管理するためのモノレポです。

## 構成

- `src/`: `admin` / `viewer` / `contracts` を束ねる Bun workspace
- `src/server`: PHP 8.5 / Laravel による API サーバー
- `src/contracts`: TypeSpec による API コントラクト
- `src/admin`: Astro / SolidJS / Elysia による管理画面
- `src/viewer`: Astro / SolidJS による閲覧サイト
- `docker/`: 開発環境用イメージとローカル設定
- `docs/adr/`: 採用済みのアーキテクチャ判断

## セットアップ

前提:

- Docker / Docker Compose
- `mise`

最初のセットアップ:

1. `mise install`
2. `mise run setup`
3. 必要な追加タスクは `mise tasks` で確認する

TypeScript 関連:

- `src/` が Bun workspace のルート
- 各 package script は `cd src && bun --filter <package> <script>` で実行する

## ローカル URL

- 管理画面: `https://local.admin.isekaijoucho.fan`
- API: `https://local.api.isekaijoucho.fan`
- 閲覧サイト: `https://local.isekaijoucho.fan`

## 変更の入口

- API の入出力やバージョンを変える: `src/contracts` から始める
- サーバー側の業務ロジックを変える: `src/server` を編集する
- 管理画面 UI や BFF を変える: `src/admin` を編集する
- 閲覧サイト UI を変える: `src/viewer` を編集する

## ドキュメント

- `ARCHITECTURE.md`: リポジトリ全体の責務分割と変更ルート
- `FRONTEND.md`: 管理画面と閲覧サイトの UI 実装方針
- `PLANS.md`: 実行計画の運用ルール
- `docs/INDEX.md`: ドキュメント索引
- `docs/adr/INDEX.md`: ADR 一覧
