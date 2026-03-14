---
applyTo: 'src/server/**'
paths:
  - 'src/server/**'
---

# サーバー規約

## パッケージマネージャー

composer。Docker コンテナ内で実行する。

## アーキテクチャ

DDD/ヘキサゴナルアーキテクチャ。依存方向は `mise run arkitect` で機械的に検証される。

- `packages/{Package}/Domain` → 他レイヤーに依存しない
- `packages/{Package}/Application` → Domain のみに依存
- `packages/{Package}/Infrastructures` → Domain に依存（Application には依存しない）
- `packages/{Package}/DebugInfrastructures` → テスト用ファイルベースリポジトリ実装

## エラーハンドリング

ドメイン/アプリケーションロジックの結果には `ResultType\Ok` と `ResultType\Err` を使用する。
期待されるビジネスロジックの失敗に対して例外をスローしない。

## テスト

テストを作成する際は以下の Skill を使用する。

- `php-unit-test-creator`: Unit テスト作成用
- `php-integration-test-creator`: Integration テスト作成用
- `php-feature-test-creator`: Feature テスト (API/Console) 作成用

Feature/Integration テストでは `DebugInfrastructures` 配下のファイルベースリポジトリを使用する。
