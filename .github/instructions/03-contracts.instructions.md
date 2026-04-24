---
name: 'Contracts Instructions'
description: 'Use when editing TypeSpec contracts, API shapes, versioned endpoints, or OpenAPI generation in src/contracts. Covers entrypoints, generated outputs, and validation.'
applyTo: 'src/contracts/**'
paths:
  - 'src/contracts/**'
---

# コントラクト規約

## 実行環境

- 依存管理とスクリプト実行には `bun` を使う
- `src/` は `admin` / `viewer` / `contracts` を束ねる Bun workspace のルート
- `contracts` の script は `src/` で `bun --filter contracts <script>` として実行する

## 構成

- `src/admin/main.tsp`: 管理画面向け API のエントリポイント
- `src/viewer/main.tsp`: 閲覧サイト向け API のエントリポイント
- `generated/oas/`: 生成された OpenAPI Specification
- `scripts/fix-enum-types.ts`: OpenAPI 生成後の補正スクリプト

## 実装規約

- TypeSpec を API 契約の Source of Truth とする
- 仕様変更時は生成物ではなく `.tsp` を編集する
- `generated/` は手動編集しない
- `tspconfig.yaml` の変更は出力先とエミッタ全体に影響するため慎重に扱う

## 検証

- `cd src && bun --filter contracts format:check`
- `cd src && bun --filter contracts test`
- 影響範囲に応じて `cd src && bun --filter contracts compile:admin` または `cd src && bun --filter contracts compile:viewer`
