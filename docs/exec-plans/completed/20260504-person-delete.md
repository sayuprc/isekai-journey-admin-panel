# Title

Person Delete 実装

## Status

completed

## Background

削除は後続の song relation 実装と依存関係を持ちやすいため、最後に単独 PR で入れる。現時点では `Person` 単体削除だけを実装し、利用中チェックの拡張は song relation 追加後に調整する

## Goal

`feature/person-delete` で `Person` の delete API を実装する

## Scope

- `src/server/app/Http/Controllers/Api/Person/DeletePersonController.php`
- `src/server/app/Http/Presenters/Api/Person/DeletePresenter.php`
- `src/server/packages/Person/Application/UseCase/Delete`
- `src/server/routes/admin.php`
- `src/server/tests/Feature/Api/Person/DeletePersonTest.php`
- `src/server/tests/Integration/Person/Application/UseCase/Delete`

## Non-Scope

- `Person` の create / read / update
- admin の `/persons` 画面追加

## Acceptance Criteria

- `/admin/v1/persons/{personId}` の DELETE で未使用 `Person` を削除できる
- 楽曲で使用中の `Person` は business logic error で削除を拒否する
- 不正 ID は validation error になる

## Steps

1. delete use case を追加した
2. controller / presenter / route を追加した
3. 楽曲で使用中の `Person` を削除しようとしたときのガードを追加した
3. feature / integration テストで delete を確認した

## Decision Log

- 2026-05-04: delete の利用中チェックは song relation 実装前に複雑化させず、必要最小限で入れる

## Validation

- `mise run api:ecs app/Http/Controllers/Api/Person/DeletePersonController.php app/Http/Presenters/Api/Person/DeletePresenter.php packages/Person/Application/UseCase/Delete packages/Person/Domain/Models/PersonRepositoryInterface.php packages/Person/Infrastructures/PersonRepository.php packages/Person/Route/PersonRouteMap.php routes/admin.php tests/Feature/Api/Person/DeletePersonTest.php tests/Integration/Person/Application/UseCase/Delete/DeleteUseCaseTest.php tests/Integration/Person/Infrastructures/PersonRepositoryTest.php`
- `docker compose exec php php artisan test --env=testing tests/Feature/Api/Person/DeletePersonTest.php tests/Integration/Person/Application/UseCase/Delete/DeleteUseCaseTest.php tests/Integration/Person/Infrastructures/PersonRepositoryTest.php`
