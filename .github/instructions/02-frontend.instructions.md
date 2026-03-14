---
applyTo: 'src/client/**'
paths:
  - 'src/client/**'
---

# クライアント規約

## パッケージマネージャー

**bun** を使用する。`npm`, `yarn`, `pnpm` は使用しない。

## フレームワーク構成

- ページ・レイアウト: Astro (`.astro`)
- インタラクティブコンポーネント: SolidJS (`.tsx`)
- BFF: Elysia (`src/server/`)

## 規約

- `src/generated/` は `mise generate` で自動生成。手動編集しない
- コーディング規約は ESLint / Stylelint で機械的に強制される
