---
applyTo: 'src/viewer/**'
paths:
  - 'src/viewer/**'
---

# ビューア規約

## パッケージマネージャー

**bun** を使用する。`npm`, `yarn`, `pnpm` は使用しない。

## フレームワーク構成

- ページ・レイアウト: Astro (`.astro`)
- インタラクティブコンポーネント: SolidJS (`.tsx`)

## 規約

- `generated/` 配下のファイルは `bun run proto:generate` で自動生成する。手動編集しない
- `src/styles/*.module.css.d.ts` は `bun run tcm` で自動生成する。手動編集しない
- 変更後は既存の `bun run lint` / `bun run style` など、影響範囲に応じたスクリプトで検証する
