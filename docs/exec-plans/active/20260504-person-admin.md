# Title

Person Admin 導線

## Status

planned

## Background

`Person` 基盤と song relation が server 側で揃っても、admin が追従しないと運用導線ができない。UI 変更は契約・server 変更と切り分けたほうがレビューしやすいため、専用 PR に分離する。

## Goal

`feature/person-admin` で `/persons` 管理画面と、song 編集 UI の `role` ベース編集導線を追加する。

## Scope

- `src/admin/src/server/routes/persons.ts`
- `src/admin/src/server/index.ts`
- `src/admin/src/server/routes/songs.ts`
- `src/admin/src/pages/persons`
- `src/admin/src/components/person`
- `src/admin/src/components/song/{CreateForm,EditableForm}.tsx`
- `src/admin/src/components/Sidebar.tsx`
- `src/admin/src/generated`

## Non-Scope

- server の `Creator` / `Performer` 削除
- creators/performers 画面の削除
- song relation の DB / domain 実装

## Acceptance Criteria

- `/persons` 一覧・作成・編集導線が追加されている
- song 編集画面で人物を選び、`role` を指定して保存できる
- admin 側の型検査が通る

## Steps

1. `src/admin/src/server/routes/persons.ts` を追加し、server index へ登録する。
2. `src/admin/src/pages/persons/{index.astro,create/index.astro,[id].astro}` と `src/admin/src/components/person/*` を追加する。
3. `src/admin/src/server/routes/songs.ts` と `src/admin/src/components/song/{CreateForm,EditableForm}.tsx` を新しい songs 契約へ合わせて更新する。
4. `src/admin/src/components/Sidebar.tsx` を更新し、`/persons` 導線を追加する。
5. 生成物追従と型検査を行う。

## Decision Log

- 2026-05-04: admin では人物選択 UI を song role ごとに別コンポーネントへ分割せず、共通の `Person` 選択 + role 指定 UI に寄せる。role 追加時の UI 重複を避けるため。

## Validation

- `mise run admin:generate`
- `(cd src/admin && bunx tsc --noEmit)`
