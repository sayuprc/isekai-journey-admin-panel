# Vitest in-source testing 導入

## Status

completed

## Background

フロントエンドのユニットテストは `*.test.ts` の分離ファイルと `bun:test` が中心で、テストのためだけの export やファイル一覧の視認性悪化が起きやすい。Vitest の in-source testing（`import.meta.vitest`）なら実装と同じファイルにテストを置け、非公開状態も export せず検証できる。一方で VSCode では型定義がないと `import.meta.vitest` に赤波線が出る。

## Goal

admin / viewer に Vitest を導入し、`import.meta.vitest` の補完が効き赤波線が出ない状態にする。単純なユーティリティは in-source へ移し、テスト専用ファイルを減らす。

## Scope

- `src/admin` / `src/viewer` への vitest 依存追加と設定
- `env.d.ts` による `vitest/importMeta` 型サポート
- 本番ビルド向け `import.meta.vitest` の dead-code elimination（astro.config）
- 単純な純関数テストの in-source 移行（admin）と viewer の非公開 helper 検証
- `test` script / mise タスク追加

## Non-Scope

- 既存のモック依存が強い `bun:test`（BFF route 等）の一括移行
- contracts / server のテスト基盤変更
- コンポーネント / E2E テスト基盤

## Acceptance Criteria

- VSCode / `tsc` で `import.meta.vitest` に型エラーが出ない
- `import.meta.vitest` から `it` / `expect` 等の補完が効く設定になっている
- admin / viewer で `bun run test`（vitest）が実行できる
- 移行した in-source テストが vitest で通る
- Astro build 時に in-source テストブロックが本番へ残らない設定がある

## Steps

- [x] vitest を catalog / admin / viewer に追加し lockfile を更新する
- [x] vitest.config / astro define / env.d.ts を整える
- [x] 単純ユーティリティを in-source へ移し、対応する `*.test.ts` を削除する
- [x] package script / mise タスクを追加する
- [x] vitest 実行と型・lint 確認を行う

## Decision Log

- 2026-07-09: 対象は admin / viewer。contracts の `bun test` と admin のモック依存 bun:test は当面残す。
- 2026-07-09: 型は `env.d.ts` の `/// <reference types="vitest/importMeta" />` で付与する（Astro の他 ambient 型を `compilerOptions.types` で狭めないため）。
- 2026-07-09: in-source 移行は純関数（auth-redirect / csrf / proxy-secret / google-id-token の decode / webauthn の passkeyErrorMessage）に限定する。
- 2026-07-09: vitest は catalog の `minimumReleaseAge`（7日）に合わせ `4.1.9` を採用。
- 2026-07-09: admin の `decodeJwtExpMs` はテスト専用 export だったため非公開化し、in-source で検証する。

## Validation

- `cd src && bun --filter admin test` → 5 files / 23 tests passed
- `cd src && bun --filter viewer test` → 1 file / 3 tests passed
- 変更ファイルの eslint 通過
- `import.meta.vitest` の型エラーなし（プロジェクト tsc で確認）
