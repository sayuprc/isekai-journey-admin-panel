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

- 期待される業務エラーは例外で表現する (ADR-0013)
  - 業務ルール違反は `BusinessRuleViolationException`、入力検証は `DomainValidationException` (集約は `Support\Domain\Validation` の `Field::of` + `Fields::validate`)、認証/認可/NotFound は `Support\UseCase\Exceptions` の各例外
  - 例外 → HTTP の変換は `App\Http\Responses\ApiExceptionRenderer` の対応表のみが担う。UseCase / Presenter で catch して詰め替えない
- ValueObject の構築は public コンストラクタ (`new`) に一本化する。不正値は `InvalidDomainException`
  - `Field` と直接 `new` の使い分けは「その場所がこの値の最初の検証境界か」で決める
    - `Field::of` を使う: その値の検証をここが初めて担い、失敗を field 名つき 422 で呼び出し主に直させる場所 (フォーム入力、OpenApiValidator を通らない CLI 入力など)
    - 直接 `new` する: 正しさを既に契約 (OpenApiValidator 済みの path パラメータ)、永続化層 (DB 復元)、ドメイン自身 (トークンの中身、生成 ID) が保証している値の表明。失敗は 500 = バグとして表面化させる
  - 一度 VO になった値は primitive に戻さず VO のまま流す。境界の検証は一度きり
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
