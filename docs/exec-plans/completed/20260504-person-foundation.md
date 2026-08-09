# Title

Person 共通基盤

## Status

completed

## Background

人物概念を `Person` に統合するための最初の PR として、`feature/person` を土台に `feature/person-foundation` で契約、DB、pure domain 定義だけを先に入れる。実行系の配線や CRUD 実装を後続 PR に分離することで、この段階ではデータ構造と契約の妥当性だけをレビューできるようにする

## Goal

`feature/person-foundation` で `Person` の契約、`persons` テーブル定義、pure domain model、生成物を追加し、後続 PR が依存できる静的な共通基盤を整える

## Scope

- `src/contracts/src/admin/persons`
- `src/contracts/src/admin/main.tsp`
- `src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`
- `src/admin/src/generated`
- `src/server/Generated`
- `src/server/database/atlas/schemas/persons.my.hcl`
- `src/server/database/atlas/atlas.hcl`
- `src/server/packages/Person/Domain/Models`
- `src/server/composer.json`
- `docs/exec-plans/active/20260504-person-*.md`

## Non-Scope

- `Person` の CRUD 実装
- `Person` の route / controller / presenter / provider 追加
- `Person` の repository / use case / test 追加
- `Creator` / `Performer` の削除
- 楽曲と人物の関係テーブル統合
- admin の `/persons` 画面追加
- 生成物に追従する song UI の変更

## Acceptance Criteria

- `src/contracts/src/admin/persons` に `Person` 契約が追加され、生成物へ反映されている
- `src/server/database/atlas/schemas/persons.my.hcl` と `atlas.hcl` に `persons` テーブル定義が追加されている
- `src/server/packages/Person/Domain/Models` に `PersonId`、`PersonName`、`Person` の pure domain model が追加されている
- runtime の route / controller / use case / repository / test はこの PR に含まれていない

## Steps

1. ✅ `src/contracts/src/admin/persons/{main,domain,service,transport}.tsp` を追加し、`src/contracts/src/admin/main.tsp` に取り込む
2. ✅ `src/server/database/atlas/schemas/persons.my.hcl` を追加し、`src/server/database/atlas/atlas.hcl` に取り込む
3. ✅ `src/server/packages/Person/Domain/Models/{PersonId,PersonName,Person}.php` を追加し、`src/server/composer.json` の autoload に `Person\\` を追加する
4. ✅ `src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`、`src/server/Generated`、`src/admin/src/generated` を更新する
5. ✅ 後続 PR の分割方針が分かるように、関連 exec plan 文書を追加・更新する

## Decision Log

- 2026-05-04: 最初の PR は runtime 実装を含めず、契約、DB、pure domain 定義、生成物だけに絞る。レビュー対象を「構造定義の妥当性」に限定するため
- 2026-05-04: `Person` service 定義は契約に含めるが、server 側の route / controller / use case 実装は後続 PR へ分離する。Source of Truth と実行系実装を段階的に揃えるため

## Validation

- `mise run contract:compile:admin`
- `mise run api:generate`
- `mise run admin:generate`
- 必要に応じて `atlas schema inspect` または schema diff で `persons` 定義の生成内容を確認する
