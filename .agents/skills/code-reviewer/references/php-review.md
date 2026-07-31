# PHP コードレビューチェックリスト

## 言語 & 基本ルール
- [ ] すべての PHP ファイルの先頭に `declare(strict_types=1);` があるか。
- [ ] PHP 8.5+ の機能（Readonly クラス/プロパティ、コンストラクタプロモーションなど）が適切に使われているか。
- [ ] すべての関数の引数と戻り値に型宣言があるか。

## エラーハンドリング (ADR-0013)
- [ ] 期待される業務エラーを例外で表現しているか（業務ルール違反は `BusinessRuleViolationException`、入力検証は `DomainValidationException`、認証/認可/NotFound は `Support\UseCase\Exceptions` の各例外）。
- [ ] 複数 field の検証エラーを `Support\Domain\Validation\FieldErrors` で集約しているか。
- [ ] 例外 → HTTP の変換を `App\Http\Responses\ApiExceptionRenderer` に任せ、UseCase / Presenter で catch して詰め替えていないか。
- [ ] ValueObject を public コンストラクタ（`new`）で構築しているか。`create()` / `reconstruct()` は存在しない。

## アーキテクチャ (ヘキサゴナル)
- **Domain Layer**:
  - [ ] Entity, Value Object, Domain Service, Repository Interface のみが含まれているか。
  - [ ] 外部フレームワーク（Laravel/Eloquent）への依存がないか。
  - [ ] ロジックが Entity/Value Object 内に適切にカプセル化されているか。
- **Application Layer**:
  - [ ] Interactor (Use Case) が含まれているか。
  - [ ] Repository Interface を使用（DI）しているか。
  - [ ] Domain と UI を分離するために InputData/OutputData や Assembler を使用しているか。
- **Infrastructures Layer**:
  - [ ] Repository Interface を実装しているか。
  - [ ] Eloquent モデルのロジックが含まれているか。
  - [ ] 外部 API クライアントが含まれているか。

## テスト
- [ ] **Unit Tests**: Mockery を使用して Domain/Application ロジックをテストしているか。
- [ ] **Integration Tests**: `DatabaseTestCase` を継承し、実際の DB を使って Infrastructure の実装をテストしているか。
- [ ] **Feature Tests**: `DatabaseTestCase` を継承し、API エンドポイントやコンソールコマンドをテストしているか。
