# Subproject Boundaries

継続的に参照するサブプロジェクトごとの責務境界をまとめる文書です。

この文書のパスは、特記がなければリポジトリルート基準で書きます。

## Contracts

- `src/contracts/`: TypeSpec による API 契約の Source of Truth
- `src/contracts/src/admin/main.tsp`: 管理画面向け契約の入口
- `src/contracts/src/viewer/main.tsp`: 閲覧サイト向け契約の入口
- `src/contracts/generated/`: 生成物。手動編集しない

## Server

- `src/server/`: PHP 8.5 / Laravel API サーバー
- `src/server/app/`: Laravel 固有の wiring
- `src/server/packages/{Package}/`: 業務ドメイン
- `src/server/database/atlas/`: Atlas によるスキーマ管理
- `src/server/tests/`: Unit / Integration / Feature テスト
- `src/server/Generated/`: OpenAPI 由来の生成コード

業務ロジックは ADR-0006 の ADOP を前提にし、`Domain`、`Application`、`Infrastructures`、`DebugInfrastructures` の境界を守ります。

## Admin

- `src/admin/`: Astro / SolidJS / Elysia による管理画面
- `src/admin/src/pages/`: Astro ページ
- `src/admin/src/layouts/`: レイアウト
- `src/admin/src/components/`: SolidJS コンポーネント
- `src/admin/src/server/`: Elysia ベースの BFF / サーバー処理
- `src/admin/src/schemas/`: 入出力スキーマ
- `src/admin/src/generated/`: OpenAPI 由来の生成クライアント

UI 実装方針の詳細は `FRONTEND.md` を参照します。

## Viewer

- `src/viewer/`: Astro / SolidJS による閲覧サイト
- `src/viewer/src/pages/`: ページ
- `src/viewer/src/layouts/`: レイアウト
- `src/viewer/src/components/`: UI コンポーネント
- `src/viewer/src/schemas/`: フロントエンド側のスキーマ
- `src/viewer/src/styles/`: スタイル

UI 実装方針の詳細は `FRONTEND.md` を参照します。
