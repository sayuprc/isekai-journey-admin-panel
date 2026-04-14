---
applyTo: 'src/contracts/**'
paths:
  - 'src/contracts/**'
---

# コントラクト規約

## パッケージマネージャー

**bun** を使用する。`npm`, `yarn`, `pnpm` は使用しない。

## プロジェクト構成

- TypeSpec で API 仕様を管理する
- OpenAPI 3.1 を生成してクライアント・管理画面・API サーバーで共有する

## 規約

- `generated/` 配下のファイルを直接編集しない（`bun run compile:*` で自動生成される）
- `tspconfig.yaml` を承認なしに変更しない（出力先・エミッタ設定の変更は全体に影響する）
