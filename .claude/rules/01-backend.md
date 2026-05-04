---
paths:
  - "src/server/**"
---

# サーバー規約

## 実行環境

- Composer / artisan / テスト / 静的解析は Docker コンテナ経由で実行する
- 共通コマンドの入口には `mise` を使う

## 構成

- `src/server/app/`: Laravel のエントリポイント、HTTP、Console、Provider などのフレームワーク接続
- `src/server/packages/{Package}/Domain`: ビジネスルール。ほかのレイヤーに依存しない
- `src/server/packages/{Package}/Application`: ユースケース。Domain にのみ依存する
- `src/server/packages/{Package}/Infrastructures`: 永続化や外部接続。Domain に依存し、Application には依存しない
- `src/server/Generated/`: OpenAPI から生成されたコード

## 実装規約

- 期待される業務エラーは例外ではなく `ResultType\Ok` / `ResultType\Err` で返す
- API 契約が変わる変更は `src/contracts` を起点に考える
- `src/server/Generated/` は手動編集しない

## テスト

- Unit テスト: `php-unit-test-creator`
- Integration テスト: `php-integration-test-creator`
- Feature テスト: `php-feature-test-creator`
- Feature / Integration テストは `DatabaseTestCase` を継承し、実際の DB を使う

## 検証

- 変更に最も近いタスクから実行する
- 代表例: `mise run api:ecs`, `mise run api:phpstan`, `mise run api:arkitect`, `mise run api:test`
