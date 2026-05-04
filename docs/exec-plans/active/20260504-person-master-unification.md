# Title

Person マスタ統合

## Status

blocked

## Background

現在は `Creator` と `Performer` が別マスタで、同一人物でも `creators` と `performers` に重複登録が必要です。実装も `src/contracts/src/admin/creators` / `performers`、`src/server/packages/Creator` / `Performer`、`src/admin/src/pages/creators` / `performers` に二重化されています。さらに楽曲側は `song_lyricists` / `song_composers` / `song_arrangers` の 3 テーブルで人物との紐づきを分散管理しており、人物統合後も役割ごとに構造が割れたままです。`docs/product-specs/20260503-song-media-admin-phase1/models.md` では長期的な `Person` 統合を前提としており、現状では同一人物の再利用と役割管理の両方が不自然です。

## Goal

`Creator` / `Performer` を廃止して `Person` マスタへ統合し、同一人物を 1 件で管理できる状態にする。あわせて楽曲と人物の紐づきは 1 テーブルへ統合し、作詞・作曲・編曲は `role` Enum で表現する。

## Scope

- API 契約: `src/contracts/src/admin/creators`, `src/contracts/src/admin/performers` の削除、追加する `src/contracts/src/admin/persons`、`src/contracts/src/admin/songs`、および生成物 `src/admin/src/generated`, `src/server/Generated`
- server ドメイン / 永続化: `src/server/packages/Creator`, `src/server/packages/Performer`, `src/server/app/Models/Creator`, `src/server/app/Models/Performer`, `src/server/database/atlas/schemas/creators.my.hcl`, `src/server/database/atlas/schemas/performers.my.hcl` の削除と、`src/server/packages/Person`, `src/server/app/Models/Person`, `src/server/database/atlas/schemas/persons.my.hcl`, `src/server/database/atlas/schemas/song-persons.my.hcl` の追加
- server 参照先調整: `src/server/packages/Song/Application/Assemble`, `src/server/packages/Song/Domain/Models`, `src/server/packages/Song/Infrastructures`, `src/server/packages/Support/Infrastructures/Mapper.php`, `src/server/routes/admin.php`, `src/server/app/Providers/Domain/*`
- admin BFF / UI: `src/admin/src/server/routes/creators.ts`, `src/admin/src/server/routes/performers.ts`, `src/admin/src/pages/creators`, `src/admin/src/pages/performers`, `src/admin/src/components/creator`, `src/admin/src/components/performer` の削除と、`src/admin/src/server/routes/persons.ts`, `src/admin/src/pages/persons`, `src/admin/src/components/person`, `src/admin/src/components/song/*`, `src/admin/src/server/routes/songs.ts` の更新

## Non-Scope

- `Event` ドメインや `EventPerformer` の新規実装
- `viewer` 側の人物表示や検索導線
- `Person` への読み、別名、活動名など追加属性の設計
- 楽曲以外の人物ロール体系の一般化

## Acceptance Criteria

- 管理画面では人物マスタが `Person` だけになり、同一人物を 1 件登録すれば楽曲と出演者用途の両方で再利用できる
- server 永続化では人物の主データが `persons` に一本化され、`creators` / `performers` テーブルとそれに依存する CRUD 実装が削除されている
- 楽曲と人物の紐づきは 1 テーブルに統合され、作詞・作曲・編曲は `role` Enum と `order_no` で区別される
- API 契約・server・admin の各層に `Person` マスタの登録 / 検索 / 取得 / 更新の入口が揃い、楽曲の登録・更新・取得は統合後も人物参照付きで成立する

## Split Plans

- `feature/person` を土台ブランチとして保持し、`feature/person-foundation`: [20260504-person-foundation.md](/tmp/person/docs/exec-plans/active/20260504-person-foundation.md)
  契約、DB、pure domain 定義、生成物のみを扱う。runtime 実装は含めない。
- `feature/person-song-relations`: [20260504-song-person-relations.md](/tmp/person/docs/exec-plans/active/20260504-song-person-relations.md)
- `feature/person-admin`: [20260504-person-admin.md](/tmp/person/docs/exec-plans/active/20260504-person-admin.md)
- `feature/remove-creator-performer`: [20260504-remove-creator-performer.md](/tmp/person/docs/exec-plans/active/20260504-remove-creator-performer.md)

## Steps

1. `src/contracts/src/admin/persons/{main,domain,service,transport}.tsp` を追加して `src/contracts/src/admin/main.tsp` に取り込み、`src/contracts/src/admin/creators` / `performers` を削除する。あわせて `src/contracts/src/admin/songs/{domain,transport}.tsp` を `lyricists` / `composers` / `arrangers` から、`role` Enum を持つ `songPersons` もしくは同等の単一配列構造へ変更する。
2. `src/server/database/atlas/schemas/persons.my.hcl` と `src/server/database/atlas/schemas/song-persons.my.hcl` を追加し、`src/server/database/atlas/schemas/{creators,performers,song-lyricists,song-composers,song-arrangers}.my.hcl` を削除する。`song-persons` は `song_id`, `person_id`, `role`, `order_no` を持つ構造にし、人物主データと楽曲紐づきをそれぞれ 1 系統へ統合する。
3. `src/server/app/Models/Person/Person.php` と `src/server/packages/Person/*` を新設し、CRUD / search / list の use case・repository・route map を実装する。`src/server/routes/admin.php` と `src/server/app/Providers/Domain/PersonServiceProvider.php` を追加して、`/admin/v1/persons` を唯一の人物 API 入口にする。
4. `src/server/packages/Creator`, `src/server/packages/Performer`, `src/server/app/Models/Creator`, `src/server/app/Models/Performer`, `src/server/app/Http/Controllers/Api/{Creator,Performer}`, `src/server/app/Http/Presenters/Api/{Creator,Performer}`、関連 provider / route / test を削除し、参照している箇所を `Person` へ置き換える。
5. `src/server/packages/Song/Domain/Models/*`、`src/server/packages/Song/Domain/Services/SongIntegrityService.php`、`src/server/packages/Song/Application/*`、`src/server/packages/Song/Infrastructures/*`、`src/server/packages/Support/Infrastructures/Mapper.php` を更新し、楽曲の人物紐づきを `creatorId` 系の 3 集合から `personId + role + orderNo` の単一コレクションへ差し替える。
6. 契約変更後に `src/server/Generated` と `src/admin/src/generated` を再生成し、server / admin の実装を新しい `persons` と `songs` 契約へ揃える。以降の実装は再生成後の型を正として進める。
7. `src/admin/src/server/routes/persons.ts` を追加して `src/admin/src/server/index.ts` に登録し、`src/admin/src/server/routes/{creators,performers}.ts` を削除する。`src/admin/src/server/routes/songs.ts` は `Person` 一覧と新しい楽曲人物配列を扱うよう更新する。
8. `src/admin/src/pages/persons/{index.astro,create/index.astro,[id].astro}`、`src/admin/src/components/person/*`、`src/admin/src/components/Sidebar.tsx` を追加・更新し、`src/admin/src/pages/creators`, `src/admin/src/pages/performers`, `src/admin/src/components/{creator,performer}/*` を削除する。`src/admin/src/components/song/{CreateForm,EditableForm}.tsx` は作詞・作曲・編曲を `role` で編集する共通 UI に置き換える。
9. `src/server/tests/Feature/Api/{Creator,Performer}`、`src/server/tests/Integration/{Creator,Performer}`、`src/server/tests/Unit/{Creator,Performer}` を削除し、`src/server/tests/Feature/Api/{Person,Song}`、`src/server/tests/Integration/{Person,Song}`、`src/server/tests/Unit/{Person,Song}` を追加・更新する。`Person` 単独 CRUD と、1 人物に複数 role を持たせた楽曲登録・更新・取得が通ることを自動化する。

## Decision Log

- 2026-05-04: `Creator` / `Performer` は互換レイヤを残さず削除する。人物概念を `Person` に一本化しないと、重複登録の根本原因が残るため。
- 2026-05-04: 楽曲と人物の関係は `song_lyricists` / `song_composers` / `song_arrangers` の 3 テーブルではなく、`role` Enum を持つ 1 テーブルへ統合する。役割追加時のスキーマ増殖を防ぎ、人物参照モデルも単純化できるため。
- 2026-05-04: 楽曲 API の公開契約も `creatorId` 系の分割配列を維持せず、`personId` と `role` を持つ単一構造へ更新する。今回の変更は破壊的だが、データモデルと API の不整合を残さないことを優先する。
- 2026-05-04: admin 導線は `/persons` を唯一の人物管理画面にし、`/creators` / `/performers` 画面は残さない。削除方針と UI 導線を揃え、重複メンテナンスをなくすため。
- 2026-05-04: レビュー容易性を優先し、この計画は umbrella plan として保持し、実装は `feature/person` を土台にした 4 本の PR に分割する。
- 2026-05-04: 最初の `feature/person-foundation` は runtime 実装を含めず、後続 PR が依存する静的定義だけに絞る。CRUD ごとの責務分離を明確にするため。

## Validation

- `mise run contract:compile:admin`
- `mise run api:generate`
- `mise run admin:generate`
- `mise run migrate:dry-run`
- `mise run migrate:testing`
- `mise run api:test -- src/server/tests/Feature/Api/Person src/server/tests/Feature/Api/Song`
- `mise run api:test -- src/server/tests/Integration/Person src/server/tests/Integration/Song`
- `mise run api:test -- src/server/tests/Unit/Person src/server/tests/Unit/Song`
- `(cd src/admin && bunx tsc --noEmit)`
- 管理画面で `/persons` から作成した 1 人を楽曲編集画面で複数 role に割り当てても、保存後の取得結果が `personId` と `role` の組み合わせで一致することを手動確認する
