# PHP インテグレーションテストパターン

このプロジェクトでは、実際のデータベースを使用したインテグレーションテストを実施しています。

## 一般的な構造

- **場所**: `src/server/tests/Integration/` に配置されます。ディレクトリ構造は対象クラスと一致させます。
- **基底クラス**: `Tests\Support\DatabaseTestCase` を継承します。
- **トレイト**: `Tests\Support\Domain\EntityFactory` と `Tests\Support\Domain\EntityStore` を使用します。
- **テストケース**: Unit テストがある場合、ハッピーパスのみを記述してください。

## データ準備 (シード)

テスト実行前に必要なデータを `EntityFactory` で生成し、`EntityStore` または実際のリポジトリで保存します。

```php
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class MyTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    // EntityStore のヘルパーを使う
    $creator = $this->createCreator($this->generateUuid(), '名前');
    $this->storeCreators($creator);

    // または直接リポジトリを使う
    $this->app->make(CreatorRepository::class)->save($creator);
}
```

## インスタンス化

モックではなく、Laravel のサービスコンテナから実体を解決します。

```php
private function getInstance(): CreateUseCase
{
    return $this->app->make(CreateUseCase::class);
}
```

## 検証 (アサーション)

戻り値の検証に加え、リポジトリの状態を確認します。

```php
// 結果の確認
$this->assertTrue($result->isOk());

// 永続化されたデータの確認
$songs = $this->app->make(SongRepository::class)->all();
$this->assertCount(1, $songs);
```

## 注意点

- `DatabaseTestCase` が `DatabaseTransactions` トレイトを持ち、各テスト後にロールバックします。
