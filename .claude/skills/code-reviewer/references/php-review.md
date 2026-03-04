# PHP コードレビューチェックリスト

## 言語 & 基本ルール
- [ ] すべての PHP ファイルの先頭に `declare(strict_types=1);` があるか。
- [ ] PHP 8.5+ の機能（Readonly クラス/プロパティ、コンストラクタプロモーションなど）が適切に使われているか。
- [ ] すべての関数の引数と戻り値に型宣言があるか。

## エラーハンドリング
- [ ] ビジネスロジックの結果に `ResultType\Ok` と `ResultType\Err` を使用しているか。
- [ ] 期待されるビジネスロジックの失敗（バリデーション失敗、エンティティ不在など）に対して例外をスローしていないか。
- [ ] 例外は予期しないシステムエラー（DB接続失敗など）のみに使用されているか。

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
  - [ ] Eloquent モデルや FileStore のロジックが含まれているか。
  - [ ] 外部 API クライアントが含まれているか。

## テスト
- [ ] **Unit Tests**: Mockery を使用して Domain/Application ロジックをテストしているか。
- [ ] **Integration Tests**: FileStore などの実体を使用して Infrastructure の実装をテストしているか。
- [ ] **Feature Tests**: API エンドポイントやコンソールコマンドをテストしているか。
