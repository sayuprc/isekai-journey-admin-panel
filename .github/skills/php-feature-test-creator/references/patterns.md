# PHP フィーチャーテストパターン

このプロジェクトでは、API エンドポイントおよび Console コマンドの Feature テストを実施しています。

## 一般的な構造

- **場所**:
  - API: `src/server/tests/Feature/Api/`
  - Console: `src/server/tests/Feature/Console/`
- **トレイト**: `Tests\Support\FileRepositoryTransaction` と `Tests\Support\Domain\EntityFactory` を使用します。
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

`factory()` メソッドを使用してファイルベースのリポジトリにデータを準備します。これは API/Console どちらのテストでも共通です。

```php
$this->factory(FileUserRepository::class, $uuid, $user);
```

## 注意点

- **Console の失敗系**: 期待されるエラーメッセージが出力されることと、`assertFailed()`（終了コード非0）を組み合わせて検証します。
- **ファイルシステム**: API 同様、実行中のデータは `storage/app/tests` に一時保存されます。
