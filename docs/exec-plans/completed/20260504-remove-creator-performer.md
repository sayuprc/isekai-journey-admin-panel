# Title

Creator Performer 削除

## Status

completed

## Background

`Person` 基盤、song relation、admin 導線が揃った後も `Creator` / `Performer` を残すと、重複メンテナンスと誤運用の入口が残る。削除は破壊的変更なので最後の cleanup PR として独立させる

## Goal

`feature/remove-creator-performer` で `Creator` / `Performer` の API、DB、UI、tests を削除し、参照先をすべて `Person` へ寄せ切る

## Scope

- `src/contracts/src/admin/creators`
- `src/contracts/src/admin/performers`
- `src/server/packages/Creator`
- `src/server/packages/Performer`
- `src/server/app/Models/{Creator,Performer}`
- `src/server/app/Http/Controllers/Api/{Creator,Performer}`
- `src/server/app/Http/Presenters/Api/{Creator,Performer}`
- `src/server/app/Providers/Domain/{CreatorServiceProvider,PerformerServiceProvider}.php`
- `src/admin/src/server/routes/{creators,performers}.ts`
- `src/admin/src/pages/{creators,performers}`
- `src/admin/src/components/{creator,performer}`
- `src/server/tests/**/*Creator*`
- `src/server/tests/**/*Performer*`

## Non-Scope

- `Person` 基盤の新規追加
- song relation の新規設計
- `/persons` の UI 追加

## Acceptance Criteria

- `Creator` / `Performer` の契約、API、DB、UI、tests が削除されている
- リポジトリ内で人物マスタの主導線が `Person` のみになっている
- 削除後も `Person` と song 関連の主要テストが通る

## Steps

1. `src/contracts/src/admin/creators` / `performers` を削除し、main から参照を外す
2. `src/server/packages/Creator` / `Performer`、関連 model / controller / presenter / provider / route を削除する
3. `src/admin/src/server/routes/{creators,performers}.ts`、`src/admin/src/pages/{creators,performers}`、`src/admin/src/components/{creator,performer}` を削除する
4. 参照切れを `Person` 基盤へ置換し、不要テストを削除する
5. 主要な `Person` / `Song` テストと admin 型検査で回帰確認する

## Decision Log

- 2026-05-04: 削除 PR は cleanup に徹し、ここでは新機能追加を混ぜない。レビューの焦点を「消してよいか」に限定するため
- 2026-05-04: admin 生成物は `src/admin/src/generated` を明示的に再生成して更新した。`admin:generate` 実行後の生成先状態に差異があったため

## Validation

- `mise run contract:compile:admin`
- `mise run api:generate`
- `mise run admin:generate`
- `(cd src/admin && bunx tsc --noEmit)`
- `mise run api:test -- tests/Feature/Api/Person`
- `mise run api:test -- tests/Feature/Api/Song`
- `mise run api:test -- tests/Integration/Person`
- `mise run api:test -- tests/Integration/Song`
- testing 用 DB 群 `isekai_observatory_testing_test_*` が未作成のため、API / Integration テストは環境起因で失敗することを確認
