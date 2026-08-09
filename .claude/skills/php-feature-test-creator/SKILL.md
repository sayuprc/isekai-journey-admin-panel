---
name: php-feature-test-creator
description: プロジェクトのフィーチャーテスト規約（API testing, Console testing, DatabaseTestCase）に従って、PHP のフィーチャーテストを作成します。「PHP のフィーチャーテストを作成して」や「API/コマンドのテストを書いて」と依頼された際に使用します。
---

# PHP Feature Test Creator

この skill は、API エンドポイントや Console コマンドの動作を検証するフィーチャーテストの作成をガイドします。

## ワークフロー

1. **対象（API/Console）の特定**:
   - **API**: パス、メソッド、パラメータ、ルート名を確認します。
   - **Console**: コマンド名（`signature`）、引数、オプションを確認します。

2. **テストファイルパスの決定**:
   - API は `src/server/tests/Feature/Api/`、Console は `src/server/tests/Feature/Console/` 配下に配置します。

3. **テストクラスの初期化**:
   - `declare(strict_types=1);` を使用し、`Tests\Support\DatabaseTestCase` を継承します。
   - `use Tests\Support\Domain\EntityFactory;` を含め、認証が必要な API は `Tests\Feature\Api\Admin\WithAuth` を使用します。

4. **テストケースの記述**:
   - `#[Test]` アトリビュートを使用します。
   - **データ準備**: `EntityFactory` で生成し、`EntityStore` のヘルパーまたはリポジトリで保存します。
   - **実行**:
     - API: `postJson()`, `getJson()` 等。
     - Console: `artisan()`。
   - **検証**:
     - API: `assertStatus()`, `assertJson()` 等。
     - Console: `expectsOutput()`, `assertSuccessful()`, `assertFailed()` 等。
   - コードのフォーマットは `mise run api:ecs:fix` で適宜フォーマット修正を実行します。

5. **テストの実行**:
   - テストの実行は `mise run api:test:feature` または `mise run api:test 作成したテストのパス` を利用します。

## リファレンス

具体的な実装パターンは、[patterns.md](references/patterns.md) を参照してください。

## 例: API と Console のテスト

### API の場合
```php
$response = $this->postJson(route(SongRouteMap::Create), $data);
$response->assertStatus(200);
```

### Console の場合
```php
$this->artisan('user:create example@example.com password')
    ->expectsOutput('ユーザーを作成しました')
    ->assertSuccessful();
```
