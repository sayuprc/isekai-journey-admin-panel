# 指示書

## プロジェクト概要
isekai-terrarium の管理画面プロジェクト。
バックエンドはパッケージごとに分割されたヘキサゴナルアーキテクチャを採用しています。

## ディレクトリ構造
- `src/client`: フロントエンド (Astro, React, TypeScript)
- `src/contracts`: API 定義(TypeSpec, git submodule で別リポジトリとして管理)
- `src/server`: バックエンド (PHP 8.5, Laravel 12, DDD/ヘキサゴナルアーキテクチャ)
- `src/server/packages`: ドメインごとのコアロジック
  - `{Package}/Application`: Interactors, UseCases
  - `{Package}/Domain`: Models (Entities/ValueObjects), Services, Repository Interfaces
  - `{Package}/Infrastructures`: 実装クラス (Eloquent/File)
- `src/server/tests`: テストコード
  - `Unit`: Mockery を使用した単体テスト
  - `Integration`: FileStore 等の実体を使用した結合テスト
  - `Feature`: API/Console のエンドポイントテスト

## 主要な規約とスキル

### 1. 一般的なコーディング規約
- **厳格な型定義**: PHP ファイルでは常に `declare(strict_types=1);` を使用してください。
- **エラーハンドリング**: ドメイン/アプリケーションロジックの結果には `ResultType\Ok` と `ResultType\Err` を使用してください。期待されるビジネスロジックの失敗に対して例外をスローしないでください。
- **アーキテクチャ**:
  - `Application`: Interactors, UseCases, InputData, Assemblers.
  - `Domain`: Entities, Value Objects, Domain Services, Repository Interfaces.
  - `Infrastructures`: 永続化 (リポジトリ実装), 外部 API クライアント.
- **フォーマット**:
  - `.editorconfig`: すべてのファイルに適用します。

### 2. PHP テスト
テストを作成する際は、以下の専用 Skill を活用してください。
- `php-unit-test-creator`: Unit テスト作成用
- `php-integration-test-creator`: Integration テスト作成用
- `php-feature-test-creator`: Feature テスト (API/Console) 作成用

### 3. インフラストラクチャ
デバッグおよびテスト用として、ファイルベースのリポジトリ（`DebugInfrastructures` 配下の `File...Repository`）が用意されています。Feature/Integration テストではこれらを使用します。

## 便利なコマンド
- `mise run test:all`: 全テスト実行
- `mise run phpstan`: 静的解析
- `mise ecs`: コーディング規約チェック
- `mise ecs:fix`: 自動修正(サーバー)
- `mise format`: 自動修正(クライアント)
- `mise generate`: `src/contracts` からコードを自動生成
