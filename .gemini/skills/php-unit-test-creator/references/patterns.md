# PHP ユニットテストパターン

このプロジェクトでは PHPUnit と Mockery を使用し、特定のアーキテクチャパターンに従っています。

## 一般的な構造

- **場所**: ユニットテストは `src/server/tests/Unit/` に配置されます。ディレクトリ構造は `src/server/app/` または `src/server/packages/` の構造と一致させます。
- **厳密な型**: 常に `declare(strict_types=1);` を含めます。
- **基底クラス**: `Tests\TestCase` を継承します。
- **PHP 8 アトリビュート**: `/** @test */` や `test` プレフィックスの代わりに `#[Test]` を使用します。

## Mockery によるモック化

依存関係は `Mockery::mock()` を使用してモック化します。

```php
private MockInterface&RepositoryInterface $repository;

protected function setUp(): void
{
    parent::setUp();
    $this->repository = Mockery::mock(RepositoryInterface::class);
}
```

### エクスペクテーション (期待値)

`shouldReceive`, `with` (複雑なロジックの場合は `withArgs`), `andReturn` を使用します。

```php
$this->repository->shouldReceive('find')
    ->with($id)
    ->andReturn($model)
    ->once();
```

## エンティティファクトリ

ドメインモデルを生成するには `Tests\Support\Domain\EntityFactory` トレイトを使用します。

```php
use Tests\Support\Domain\EntityFactory;

class MyTest extends TestCase
{
    use EntityFactory;
    
    // ...
    $song = $this->createSong(...);
}
```

## アサーション

- 標準的な PHPUnit アサーション（`assertEquals`, `assertSame`, `assertInstanceOf` など）も使用します。
