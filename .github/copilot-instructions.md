# 指示書

## プロジェクト概要

isekai-terrarium の管理画面プロジェクト。
`src/server` (PHP/Laravel)、`src/client` (Astro/SolidJS)、`src/contracts` (TypeSpec, git submodule) で構成。

## 検証

変更後は `mise tasks` で関連する検証タスクを確認し、実行すること。

## ADR

アーキテクチャ決定記録は `docs/adr/` にあります（`mise run adr:pull` で取得・更新）。

## 禁止事項

- リンター・フォーマッター設定ファイルの変更（コードを修正すること）
- `src/server/Generated/`, `src/client/src/generated/` の手動編集
- `git commit --no-verify` の使用
- `npm`, `yarn`, `pnpm` の使用（`bun` を使うこと）
