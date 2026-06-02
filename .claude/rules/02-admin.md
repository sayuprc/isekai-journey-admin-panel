---
paths:
  - "src/admin/**"
---

# 管理画面規約

## 実行環境

- 依存管理には `pnpm` を使い、インストールは root から `mise run pnpm:install` を実行する
- スクリプト実行には `bun` を使う
- `src/` は `admin` / `viewer` / `contracts` を束ねる pnpm workspace のルート
- 共有タスクは `mise`、パッケージ固有タスクは `src/` で `bun --filter admin <script>` として実行する

## 構成

- `src/admin/src/pages/`: Astro のページとルーティング
- `src/admin/src/layouts/`: ページレイアウト
- `src/admin/src/components/`: SolidJS コンポーネント
- `src/admin/src/server/`: 管理画面専用の BFF / サーバー側処理
- `src/admin/src/schemas/`: フォームや入出力のスキーマ
- `src/admin/src/generated/`: OpenAPI から生成された API クライアント

## 実装規約

- ページ責務は `.astro` に保ち、対話的な UI は `.tsx` に分離する
- API クライアントや型は `src/admin/src/generated/` を Source of Truth とし、手動編集しない
- API shape を変える場合は `src/contracts` を更新してから `mise run admin:generate` を使う

## 検証

- `cd src && bun --filter admin lint:check`
- `cd src && bun --filter admin style:check`
- `cd src && bun --filter admin build`
