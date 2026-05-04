# Title

Person 共通基盤

## Status

completed

## Background

人物概念を `Person` に統合するための最初の PR として、`feature/person` を土台に `feature/person-foundation` で既存 `Creator` / `Performer` をまだ壊さずに共通基盤だけを先に入れる。ここで契約、DB、server の人物基盤を先に確定しておくと、後続の楽曲統合と UI 変更のレビュー差分を小さくできる。

## Goal

`feature/person-foundation` で `persons` 主テーブルと `Person` API を追加し、後続 PR が依存できる共通基盤を整える。

## Scope

- `src/contracts/src/admin/persons`
- `src/contracts/src/admin/main.tsp`
- `src/server/database/atlas/schemas/persons.my.hcl`
- `src/server/app/Models/Person`
- `src/server/packages/Person`
- `src/server/app/Providers/Domain/PersonServiceProvider.php`
- `src/server/routes/admin.php`
- `src/server/tests/Feature/Api/Person`
- `src/server/tests/Integration/Person`
- `src/server/tests/Unit/Person`

## Non-Scope

- `Creator` / `Performer` の削除
- 楽曲と人物の関係テーブル統合
- admin の `/persons` 画面追加
- 生成物に追従する song UI の変更

## Acceptance Criteria

- `persons` テーブルと `Person` CRUD / list / search API が追加されている
- `src/server/Generated` と `src/admin/src/generated` に `Person` 契約生成物が追加されている
- 既存 `Creator` / `Performer` / `Song` API はこの PR では壊れていない

## Steps

1. ✅ `src/contracts/src/admin/persons/{main,domain,service,transport}.tsp` を追加し、`src/contracts/src/admin/main.tsp` に取り込む。
2. ✅ `src/server/database/atlas/schemas/persons.my.hcl` と `src/server/app/Models/Person/Person.php` を追加する。
3. ✅ `src/server/packages/Person/*`、`src/server/app/Providers/Domain/PersonServiceProvider.php`、`src/server/routes/admin.php` を追加し、`/admin/v1/persons` を実装する。
4. ✅ `src/server/tests/Feature/Api/Person`、`src/server/tests/Integration/Person`、`src/server/tests/Unit/Person` を追加する。
5. ✅ 生成物を更新し、`Person` API の最小検証を通す。

## Decision Log

- 2026-05-04: まずは `Person` だけを追加し、既存人物機能の削除は別 PR に後ろ倒しする。基盤導入と破壊的削除を分離してレビューしやすくするため。

## Validation

- `mise run contract:compile:admin`
- `mise run api:generate`
- `mise run admin:generate`
- `mise run api:test -- src/server/tests/Feature/Api/Person`
- `mise run api:test -- src/server/tests/Integration/Person`
- `mise run api:test -- src/server/tests/Unit/Person`
- 実施メモ: testing DB には `atlas schema apply --env testing --auto-approve` を実行し、feature は `docker compose exec php php artisan test --env=testing tests/Feature/Api/Person/CreatePersonTest.php`、integration / unit は `docker compose exec php ./vendor/bin/paratest ...` で確認した
