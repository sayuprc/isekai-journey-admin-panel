# Title

Person Read 実装

## Status

completed

## Background

`create` 実装後、人物の一覧・検索・単体取得がないと admin や後続機能で `Person` を参照できない。read 系は同じ repository を共有するため、list / search / get を 1 PR にまとめる。

## Goal

`feature/person-read` で `Person` の list / search / get API を実装する。

## Scope

- `src/server/app/Http/Controllers/Api/Person/{ListPersonController,SearchPersonController,GetPersonController}.php`
- `src/server/app/Http/Presenters/Api/Person/{ListPresenter,SearchPresenter,GetPresenter}.php`
- `src/server/packages/Person/Application/UseCase/{List,Search,Get}`
- `src/server/packages/Person/Domain/Criteria`
- `src/server/routes/admin.php`
- `src/server/tests/Feature/Api/Person/{ListPersonTest,SearchPersonTest,GetPersonTest}.php`
- `src/server/tests/Integration/Person/Application/UseCase/{List,Search,Get}`

## Non-Scope

- `Person` の create / update / delete
- admin の `/persons` 画面追加

## Acceptance Criteria

- `/admin/v1/persons` の GET、`/admin/v1/persons/search`、`/admin/v1/persons/{personId}` が動作する
- 名前検索と sort 条件が `Person` 契約どおりに機能する

## Steps

1. `Person` の criteria と list / search / get use case を追加した。
2. controller / presenter を追加した。
3. route と provider を read 導線へ拡張した。
4. feature / integration テストで list / search / get を確認した。

## Decision Log

- 2026-05-04: read は list / search / get を 1 PR にまとめる。repository と presenter の重複差分を減らすため。

## Validation

- `mise run api:ecs app/Http/Controllers/Api/Person app/Http/Presenters/Api/Person app/Providers/Domain/PersonServiceProvider.php packages/Person routes/admin.php tests/Feature/Api/Person tests/Integration/Person tests/Support/Domain/EntityStore.php`
- `docker compose exec php php artisan test --env=testing tests/Feature/Api/Person/ListPersonTest.php tests/Feature/Api/Person/SearchPersonTest.php tests/Feature/Api/Person/GetPersonTest.php tests/Integration/Person/Application/UseCase/List/ListUseCaseTest.php tests/Integration/Person/Application/UseCase/Search/SearchUseCaseTest.php tests/Integration/Person/Application/UseCase/Get/GetUseCaseTest.php tests/Integration/Person/Infrastructures/PersonRepositoryTest.php`
