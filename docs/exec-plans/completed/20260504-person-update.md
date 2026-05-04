# Title

Person Update 実装

## Status

completed

## Background

`Person` を登録・参照できても更新できなければ運用上の修正ができない。update は create の integrity service と repository を再利用するため、単独 PR に切り出しやすい。

## Goal

`feature/person-update` で `Person` の update API を実装する。

## Scope

- `src/server/app/Http/Controllers/Api/Person/UpdatePersonController.php`
- `src/server/app/Http/Presenters/Api/Person/UpdatePresenter.php`
- `src/server/app/Providers/Domain/PersonServiceProvider.php`
- `src/server/packages/Person/Application/UseCase/Update`
- `src/server/routes/admin.php`
- `src/server/tests/Feature/Api/Person/UpdatePersonTest.php`
- `src/server/tests/Integration/Person/Application/UseCase/Update`

## Non-Scope

- `Person` の create / read / delete
- admin の `/persons` 画面追加

## Acceptance Criteria

- `/admin/v1/persons/{personId}` の PUT で `name` と `orderNo` を更新できる
- 同名競合は business rule error になる

## Steps

1. update use case と input / output を追加した。
2. controller / presenter / provider / route を更新した。
3. feature / integration テストで update を確認した。

## Decision Log

- 2026-05-04: update は create と同じ integrity service を再利用し、重複ロジックを増やさない。

## Validation

- `mise run api:ecs app/Http/Controllers/Api/Person/UpdatePersonController.php app/Http/Presenters/Api/Person/UpdatePresenter.php app/Providers/Domain/PersonServiceProvider.php packages/Person/Application/UseCase/Update packages/Person/Domain/Services/PersonIntegrityService.php packages/Person/Route/PersonRouteMap.php routes/admin.php tests/Feature/Api/Person/UpdatePersonTest.php tests/Integration/Person/Application/UseCase/Update/UpdateUseCaseTest.php`
- `docker compose exec php php artisan test --env=testing tests/Feature/Api/Person/UpdatePersonTest.php tests/Integration/Person/Application/UseCase/Update/UpdateUseCaseTest.php`
