---
name: php-integration-test-creator
description: プロジェクトのインテグレーションテスト規約に従って、PHP のインテグレーションテストを作成します。「PHP のインテグレーションテストを作成して」と依頼された際に使用します。
---

# PHP Integration Test Creator

この skill は、ファイルベースのデバッグ用リポジトリを使用したインテグレーションテストの作成をガイドします。

## ワークフロー

1. **対象クラスの分析**:
   - 必要な依存関係を確認し、それらに対応する `DebugInfrastructures`（`File...Repository`）が存在することを確認します。

2. **テストファイルパスの決定**:
   - `src/server/tests/Integration/` 配下に、対象クラスの構造に合わせて配置します。

3. **テストクラスの初期化**:
   - `declare(strict_types=1);` を使用します。
   - `Tests\TestCase` を継承します。
   - `use Tests\Support\FileRepositoryTransaction;` と `use Tests\Support\Domain\EntityFactory;` を含めます。
   - `getInstance()` メソッドで `$this->app->make(TargetClass::class)` を使用してインスタンスを取得するように実装します。

4. **テストケースの記述**:
   - `#[Test]` アトリビュートを使用します。
   - **データ準備**: `factory()` メソッドを使用して、リポジトリに必要なデータを投入します。
   - **実行**: `handle()` メソッドなどを呼び出します。
   - **検証**:
     - 戻り値（`ResultType` など）をアサートします。
     - `getAll()` メソッドを使用して、ファイルストアに正しく永続化されたかを確認します。
   - コードのフォーマットは `mise run ecs:fix` で適宜フォーマット修正を実行します。

5. **テストの実行**:
   - テストの実行は `mise run test:integration` または `mise run test 作成したテストのパス` を利用します。

## リファレンス

具体的な実装コードやファイルリポジトリの使い方は、[patterns.md](references/patterns.md) を参照してください。

## 例: Interactor のインテグレーションテスト

```php
<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\CreateInteractor;
use Song\DebugInfrastructures\FileSongRepository;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function create(): void
    {
        // 1. データ準備
        // $this->factory(...);

        // 2. 実行
        $result = $this->getInstance()->handle($input);

        // 3. 検証
        $this->assertTrue($result->isOk());
        $this->assertCount(1, $this->getAll(FileSongRepository::class));
    }

    private function getInstance(): CreateInteractor
    {
        return $this->app->make(CreateInteractor::class);
    }
}
```
