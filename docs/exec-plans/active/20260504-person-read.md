# Title

Person Read 実装

## Status

planned

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

1. `Person` の criteria と list / search / get use case を追加する。
2. controller / presenter を追加する。
3. route と provider を read 導線へ拡張する。
4. feature / integration テストで list / search / get を確認する。

## Decision Log

- 2026-05-04: read は list / search / get を 1 PR にまとめる。repository と presenter の重複差分を減らすため。

## Validation

- `docker compose exec php php artisan test --env=testing tests/Feature/Api/Person/ListPersonTest.php`
- `docker compose exec php php artisan test --env=testing tests/Feature/Api/Person/SearchPersonTest.php`
- `docker compose exec php php artisan test --env=testing tests/Feature/Api/Person/GetPersonTest.php`
