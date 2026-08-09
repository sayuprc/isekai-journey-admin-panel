---
name: php-integration-test-creator
description: プロジェクトのインテグレーションテスト規約に従って、PHP のインテグレーションテストを作成します。「PHP のインテグレーションテストを作成して」と依頼された際に使用します
---

# PHP Integration Test Creator

この skill は、実際のデータベースを使用したインテグレーションテストの作成をガイドします

## ワークフロー

1. **対象クラスの分析**:
   - 必要な依存関係と、対象が扱う集約・テーブルを確認します

2. **テストファイルパスの決定**:
   - `src/server/tests/Integration/` 配下に、対象クラスの構造に合わせて配置します

3. **テストクラスの初期化**:
   - `declare(strict_types=1);` を使用します
   - `Tests\Support\DatabaseTestCase` を継承します
   - `use Tests\Support\Domain\EntityFactory;` と `use Tests\Support\Domain\EntityStore;` を含めます
   - `getInstance()` メソッドで `$this->app->make(TargetClass::class)` を使用してインスタンスを取得するように実装します

4. **テストケースの記述**:
   - `#[Test]` アトリビュートを使用します
   - **データ準備**: `EntityFactory` で生成し、`EntityStore` のヘルパーまたはリポジトリで保存します
   - **実行**: `handle()` メソッドなどを呼び出します
   - **検証**:
     - 戻り値の OutputData、または送出される例外 (`expectException()`) をアサートします
     - `assertDatabaseHas()` やリポジトリを使用して、正しく永続化されたかを確認します
   - コードのフォーマットは `mise run api:ecs:fix` で適宜フォーマット修正を実行します

5. **テストの実行**:
   - テストの実行は `mise run api:test:integration` または `mise run api:test 作成したテストのパス` を利用します

## リファレンス

具体的な実装コードやファイルリポジトリの使い方は、[patterns.md](references/patterns.md) を参照してください

## 例: UseCase のインテグレーションテスト

```php
<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase\Create;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Create\CreateInputData;
use Song\Application\Admin\UseCase\Create\CreateUseCase;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canCreate(): void
    {
        // 1. データ準備
        // $this->storePersons($this->createPerson(...));

        // 2. 実行
        $result = $this->getInstance()->handle(new CreateInputData(/* ... */));

        // 3. 検証
        $this->assertSame('テスト楽曲', $result->song->title->value);
        $this->assertDatabaseHas('songs', ['title' => 'テスト楽曲']);
    }

    private function getInstance(): CreateUseCase
    {
        return $this->app->make(CreateUseCase::class);
    }
}
```
