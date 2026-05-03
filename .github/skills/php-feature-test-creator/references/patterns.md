# PHP フィーチャーテストパターン

このプロジェクトでは、API エンドポイントおよび Console コマンドの Feature テストを実施しています。

## 一般的な構造

- **場所**:
  - API: `src/server/tests/Feature/Api/`
  - Console: `src/server/tests/Feature/Console/`
- **基底クラス**: `Tests\Support\DatabaseTestCase` を継承します。
- **トレイト**: `Tests\Support\Domain\EntityFactory` と `Tests\Support\Domain\EntityStore` を必要に応じて使用します。
- **テストケース**: 起こりうるパターンをできるだけ記述してください。

## API テスト

Laravel の HTTP テストヘルパーとルーティングを使用します。

```php
use Song\Route\SongRouteMap;

$response = $this->postJson(route(SongRouteMap::Create), [
    'title' => '曲名',
]);

$response->assertStatus(200)
    ->assertJson([ ... ]);
```

## Console テスト

`artisan()` ヘルパーを使用してコマンドの実行と出力を検証します。

```php
$this->artisan('command:signature argument --option=value')
    ->expectsOutput('期待される出力メッセージ')
    ->assertSuccessful(); // 終了コード 0 を期待
```

## データ準備

`EntityFactory` でエンティティを生成し、`EntityStore` または実際のリポジトリを使ってDBに保存します。

```php
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class MySongTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    public function setUp(): void
    {
        parent::setUp();
        $this->storeSongs($this->createSong(...));
    }
}
```

直接リポジトリを使う場合:

```php
$this->app->make(SongRepository::class)->save($this->createSong(...));
```

## 注意点

- **Console の失敗系**: 期待されるエラーメッセージが出力されることと、`assertFailed()`（終了コード非0）を組み合わせて検証します。
