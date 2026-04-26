---
name: php-unit-test-creator
description: プロジェクトのテスト規約に従って、Laravel/PHP のユニットテストを作成します。「PHP のユニットテストを作成して」や「クラスのテストを書いて」と依頼された際に使用します。
---

# PHP Unit Test Creator

この skill は、プロジェクトの既存のアーキテクチャや規約に則った、高品質な PHP ユニットテストの作成をガイドします。

## ワークフロー

1. **対象クラスの分析**:
   - クラスの名前空間、依存関係（コンストラクタ引数）、および公開メソッドを特定します。
   - 戻り値の型を確認します。

2. **テストファイルパスの決定**:
   - テストは `src/server/tests/Unit/` 配下に、対象クラスのディレクトリ構造を模して配置します。
   - 例: `src/server/packages/Song/Application/Interactors/CreateInteractor.php` -> `src/server/tests/Unit/Song/Application/Interactors/CreateInteractorTest.php`

3. **テストクラスの初期化**:
   - `declare(strict_types=1);` を使用します。
   - `Tests\TestCase` を継承します。
   - ドメインモデルの生成が必要な場合は `use Tests\Support\Domain\EntityFactory;` を含めます。
   - モック用のプライベートプロパティを `MockInterface&ClassName` 形式で定義します。
   - `setUp()` を実装し、`Mockery::mock()` を使用してモックを初期化します。
   - 対象クラスを依存関係と共にインスタンス化する `getInstance()` を実装します。

4. **テストケースの記述**:
   - 各テストメソッドには `#[Test]` アトリビュートを使用します。
   - 以下の網羅を目指します：
     - **正常系**: 有効な入力、期待される振る舞い。
     - **異常系**: 無効な入力、依存先の失敗（例: リポジトリが `Err` を返す場合）。
   - Mockery のエクスペクテーション（`shouldReceive`, `once`, `andReturn`）を使用します。
   - `$this->assertTrue($result->isOk())` または標準的な PHPUnit のアサーションを使用して結果を検証します。
   - コードのフォーマットは `mise run ecs:fix` で適宜フォーマット修正を実行します。

5. **テストの実行**:
   - テストの実行は `mise run test:unit` または `mise run test 作成したテストのパス` を利用します。

## リファレンス

具体的なコードパターン、モックのエクスペクテーション、共通のアサーションについては、[patterns.md](references/patterns.md) を参照してください。

## 例: Interactor のテスト

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Ok;
use Song\Application\Interactors\CreateInteractor;
use Song\Domain\Models\SongRepositoryInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(SongRepositoryInterface::class);
    }

    #[Test]
    public function create(): void
    {
        // ... テストロジック ...
    }

    private function getInstance(): CreateInteractor
    {
        return new CreateInteractor($this->repository);
    }
}
```
