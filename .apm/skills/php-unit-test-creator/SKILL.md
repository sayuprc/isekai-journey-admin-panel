---
name: php-unit-test-creator
description: プロジェクトのテスト規約に従って、Laravel/PHP のユニットテストを作成します。「PHP のユニットテストを作成して」や「クラスのテストを書いて」と依頼された際に使用します
---

# PHP Unit Test Creator

この skill は、プロジェクトの既存のアーキテクチャや規約に則った、高品質な PHP ユニットテストの作成をガイドします

## ワークフロー

1. **対象クラスの分析**:
   - クラスの名前空間、依存関係(コンストラクタ引数)、および公開メソッドを特定します
   - 戻り値の型を確認します

2. **テストファイルパスの決定**:
   - テストは `src/server/tests/Unit/` 配下に、対象クラスのディレクトリ構造を模して配置します
   - 例: `src/server/packages/Song/Application/Admin/UseCase/Create/CreateUseCase.php` -> `src/server/tests/Unit/Song/Application/Admin/UseCase/CreateUseCaseTest.php`

3. **テストクラスの初期化**:
   - `declare(strict_types=1);` を使用します
   - `Tests\TestCase` を継承します
   - ドメインモデルの生成が必要な場合は `use Tests\Support\Domain\EntityFactory;` を含めます
   - モック用のプライベートプロパティを `MockInterface&ClassName` 形式で定義します
   - `setUp()` を実装し、`Mockery::mock()` を使用してモックを初期化します
   - 対象クラスを依存関係と共にインスタンス化する `getInstance()` を実装します

4. **テストケースの記述**:
   - 各テストメソッドには `#[Test]` アトリビュートを使用します
   - 以下の網羅を目指します：
     - **正常系**: 有効な入力、期待される振る舞い
     - **異常系**: 無効な入力、依存先の失敗(例: サービスが `BusinessRuleViolationException` を投げる場合)
   - Mockery のエクスペクテーション(`shouldReceive`, `once`, `andReturn`)を使用します
   - 例外を期待する場合は `expectException()`、それ以外は標準的な PHPUnit のアサーションを使用して結果を検証します
   - コードのフォーマットは `mise run api:ecs:fix` で適宜フォーマット修正を実行します

5. **テストの実行**:
   - テストの実行は `mise run api:test:unit` または `mise run api:test 作成したテストのパス` を利用します

## リファレンス

具体的なコードパターン、モックのエクスペクテーション、共通のアサーションについては、[patterns.md](references/patterns.md) を参照してください

## 例: UseCase のテスト

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\UseCase;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Create\CreateUseCase;
use Song\Domain\Services\SongIntegrityService;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreateUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongIntegrityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Mockery::mock(SongIntegrityService::class);
    }

    #[Test]
    public function create(): void
    {
        // ... テストロジック ...
    }

    private function getInstance(): CreateUseCase
    {
        return new CreateUseCase($this->service);
    }
}
```
