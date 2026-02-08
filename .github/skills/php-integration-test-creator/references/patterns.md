# PHP インテグレーションテストパターン

このプロジェクトでは、ファイルベースのデバッグ用インフラストラクチャを使用したインテグレーションテストを実施しています。

## 一般的な構造

- **場所**: `src/server/tests/Integration/` に配置されます。ディレクトリ構造は対象クラスと一致させます。
- **トレイト**: 常に `Tests\Support\FileRepositoryTransaction` と `Tests\Support\Domain\EntityFactory` を使用します。

## データ準備 (シード)

テスト実行前に必要なデータを `factory` メソッドを使用して準備します。

```php
// エンティティの作成
$creator = $this->createCreator($this->generateUuid(), '名前');

// ファイルストアへの保存 (factory メソッドを使用)
// 第1引数には DebugInfrastructures 配下のファイルリポジトリクラスを指定します
$this->factory(FileCreatorRepository::class, $creator->creatorId->value, $creator);
```

## インスタンス化

モックではなく、Laravel のサービスコンテナから実体を解決します。

```php
private function getInstance(): CreateInteractor
{
    return $this->app->make(CreateInteractor::class);
}
```

## 検証 (アサーション)

戻り値の検証に加え、リポジトリ（ファイルストア）の状態を検証します。

```php
// 結果の確認
$this->assertTrue($result->isOk());

// 永続化されたデータの確認
/** @var array<Song> */
$songs = $this->getAll(FileSongRepository::class);
$this->assertCount(1, $songs);
```

## 注意点

- 実際のデータベースではなく `storage/app/tests` 配下のファイルを使用するため、`DebugInfrastructures` のリポジトリクラスを使用してください。
- `FileRepositoryTransaction` が `setUp` で環境を整え、`tearDown` でファイルを削除します。
