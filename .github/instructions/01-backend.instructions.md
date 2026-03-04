---
applyTo: 'src/server/**'
---

# サーバー規約

## 技術スタック

- PHP 8.5
- Laravel 12
- DDD/ヘキサゴナルアーキテクチャ

## ディレクトリ構造

- `packages/`: ドメインごとのコアロジック
  - `{Package}/Application`: Interactors, UseCases, InputData, Assemblers
  - `{Package}/Domain`: Models (Entities/ValueObjects), Services, Repository Interfaces
  - `{Package}/Infrastructures`: 実装クラス (Eloquent/File)
  - `{Package}/DebugInfrastructures`: テスト・デバッグ用ファイルベースリポジトリ
  - `{Package}/Route`: ルート定義
- `tests/`: テストコード
  - `Unit`: Mockery を使用した単体テスト
  - `Integration`: FileStore 等の実体を使用した結合テスト
  - `Feature`: API/Console のエンドポイントテスト

## コーディング規約

### 型定義

PHP ファイルでは常に `declare(strict_types=1);` を使用する。

### エラーハンドリング

ドメイン/アプリケーションロジックの結果には `ResultType\Ok` と `ResultType\Err` を使用する。
期待されるビジネスロジックの失敗に対して例外をスローしない。

### アーキテクチャ

- `Application`: Interactors, UseCases, InputData, Assemblers
- `Domain`: Entities, Value Objects, Domain Services, Repository Interfaces
- `Infrastructures`: 永続化 (リポジトリ実装), 外部 API クライアント

## テスト

テストを作成する際は以下の Skill を使用する。
- `php-unit-test-creator`: Unit テスト作成用
- `php-integration-test-creator`: Integration テスト作成用
- `php-feature-test-creator`: Feature テスト (API/Console) 作成用

Feature/Integration テストでは `DebugInfrastructures` 配下のファイルベースリポジトリ (`File...Repository`) を使用する。
