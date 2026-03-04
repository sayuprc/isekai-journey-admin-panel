# 指示書

## プロジェクト概要

isekai-terrarium の管理画面プロジェクト。
フロントエンド (Astro/SolidJS)、バックエンド (PHP/Laravel)、API 定義 (TypeSpec) の 3 つで構成されています。

## ディレクトリ構造

- `src/client`: フロントエンド (Astro, SolidJS, Elysia BFF) → 詳細は `src/client/CLAUDE.md`
- `src/contracts`: API 定義 (TypeSpec, git submodule で別リポジトリとして管理)
- `src/server`: バックエンド (PHP 8.5, Laravel 12) → 詳細は `src/server/CLAUDE.md`

## 共通規約

- **フォーマット**: `.editorconfig` をすべてのファイルに適用します。

## 便利なコマンド

- `mise run test:all`: 全テスト実行
- `mise run phpstan`: 静的解析
- `mise ecs`: コーディング規約チェック (サーバー)
- `mise ecs:fix`: 自動修正 (サーバー)
- `mise format`: 自動修正 (クライアント)
- `mise generate`: `src/contracts` からコードを自動生成
