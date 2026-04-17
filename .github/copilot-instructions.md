# 指示書

## プロジェクト概要

isekai-observatory のプロジェクト。
`src/server` (PHP/Laravel)、`src/admin` (Astro/SolidJS/ElysiaJS)、`src/viewer` (Astro/SolidJS)、`src/contracts` (TypeSpec) で構成。

## 検証

変更後はルートまたは対象パッケージの `mise tasks` と既存の package/composer scripts で関連する検証タスクを確認し、実行すること。

## ADR

アーキテクチャ決定記録は `docs/adr/` にあります。

## 禁止事項

- リンター・フォーマッター設定ファイルの変更（コードを修正すること）
- `src/server/Generated/`, `src/contracts/generated/`, `src/admin/src/generated/`, `src/viewer/generated/` の手動編集
- `git commit --no-verify` の使用
- `npm`, `yarn`, `pnpm` の使用（`bun` を使うこと）
