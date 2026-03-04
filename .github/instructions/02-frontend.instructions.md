---
applyTo: 'src/client/**'
---

# クライアント規約

## 技術スタック

- **Astro**: SSR モード、ページ・レイアウト
- **SolidJS**: インタラクティブなコンポーネント (`.tsx`)
- **Elysia**: BFF (Backend for Frontend) サーバー
- **Tailwind CSS v4 + DaisyUI v5**: スタイリング
- **TypeScript**: 厳格な型定義

## ディレクトリ構造 (`src/`)

- `pages/`: Astro ページ。ルーティングはファイルシステムベース
  - `api/[...slugs].ts`: Elysia BFF のエントリーポイント
- `components/`: SolidJS コンポーネント
- `layouts/`: Astro レイアウト
- `server/`: Elysia BFF サーバーのロジック
  - `index.ts`: Elysia アプリ定義
  - `routes/`: ドメインごとのルート定義
  - `middleware.ts`: 認証ガード (セッション + CSRF 検証)
  - `client.ts`: PHP バックエンド API への接続クライアント
  - `errors.ts`: API エラーハンドリング
  - `redis.ts`: セッション管理 (Upstash Redis)
- `utils/`: ユーティリティ関数
- `generated/schema.d.ts`: OpenAPI から自動生成された型定義 (編集不可)

## コーディング規約

- TypeScript の strict モードを使用
- ESLint (`@stylistic/eslint-plugin`) でフォーマット統一
- CSS の順序は `stylelint-config-recess-order` に従う
