# Title

Person Create 実装

## Status

completed

## Background

`Person` の契約、DB、pure domain 定義は `feature/person-foundation` で追加済みだが、まだ runtime 実装は存在しない。CRUD 単位でレビューしやすくするため、最初に create だけを切り出して実装する

## Goal

`feature/person-create` で `Person` の作成 API を server 側に実装し、生成済み契約に沿って人物を 1 件登録できる状態にする

## Scope

- `src/server/app/Http/Controllers/Api/Person/CreatePersonController.php`
- `src/server/app/Http/Presenters/Api/Person/{Converter,CreatePresenter}.php`
- `src/server/app/Models/Person/Person.php`
- `src/server/app/Providers/Domain/PersonServiceProvider.php`
- `src/server/bootstrap/providers.php`
- `src/server/packages/AdminUser/Domain/Models/Permission.php`
- `src/server/packages/Person/Application/UseCase/Create`
- `src/server/packages/Person/Domain/Services/PersonIntegrityService.php`
- `src/server/packages/Person/Domain/Models/PersonRepositoryInterface.php`
- `src/server/packages/Person/Infrastructures/PersonRepository.php`
- `src/server/packages/Person/Route/PersonRouteMap.php`
- `src/server/routes/admin.php`
- `src/server/tests/Feature/Api/Person/CreatePersonTest.php`
- `src/server/tests/Integration/Person/Application/UseCase/Create`
- `src/server/tests/Integration/Person/Infrastructures/PersonRepositoryTest.php`
- `src/server/tests/Support/Domain/EntityFactory.php`

## Non-Scope

- `Person` の list / search / get
- `Person` の update / delete
- admin の `/persons` 画面追加
- `Creator` / `Performer` の削除

## Acceptance Criteria

- `/admin/v1/persons` への POST で `Person` を 1 件作成できる
- 同名 `Person` の重複作成は business rule error になる
- create 実装に必要な repository / model / provider / route が追加されている
- feature / integration テストで create 導線が確認できる

## Steps

1. ✅ `src/server/app/Models/Person/Person.php`、`src/server/packages/Person/Domain/Models/PersonRepositoryInterface.php`、`src/server/packages/Person/Infrastructures/PersonRepository.php` を追加し、`persons` テーブルを永続化対象として扱えるようにする
2. ✅ `src/server/packages/Person/Domain/Services/PersonIntegrityService.php` と `src/server/packages/Person/Application/UseCase/Create/*` を追加し、名前重複検証を含む create use case を実装する
3. ✅ `src/server/app/Http/Controllers/Api/Person/CreatePersonController.php`、`src/server/app/Http/Presenters/Api/Person/{Converter,CreatePresenter}.php`、`src/server/packages/Person/Route/PersonRouteMap.php` を追加し、OpenAPI 生成物に沿った create 応答を返す
4. ✅ `src/server/app/Providers/Domain/PersonServiceProvider.php`、`src/server/bootstrap/providers.php`、`src/server/routes/admin.php`、`src/server/packages/AdminUser/Domain/Models/Permission.php` を更新し、create ルートと DI を配線する
5. ✅ `src/server/tests/Feature/Api/Person/CreatePersonTest.php`、`src/server/tests/Integration/Person/Application/UseCase/Create/*`、`src/server/tests/Integration/Person/Infrastructures/PersonRepositoryTest.php`、`src/server/tests/Support/Domain/EntityFactory.php` を追加・更新して、create 導線を検証する

## Decision Log

- 2026-05-04: CRUD は 1 PR 1 機能に分け、`create` を最初に実装する。以降の read / update / delete が共通基盤の使い方を揃えやすくするため

## Validation

- `docker compose exec php composer dump-autoload`
- `docker compose exec php php artisan test --env=testing tests/Feature/Api/Person/CreatePersonTest.php`
- `docker compose exec php php artisan test --env=testing tests/Integration/Person/Application/UseCase/Create/CreateUseCaseTest.php`
- `docker compose exec php php artisan test --env=testing tests/Integration/Person/Infrastructures/PersonRepositoryTest.php`
