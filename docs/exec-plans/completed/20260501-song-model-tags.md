# Title

楽曲モデルへの楽曲タグ関連付け

## Status

completed

## Background

楽曲タグは `song_tags` マスタ、ドメインモデル、CRUD API、管理画面の一覧・編集画面まで整っている。一方で楽曲本体の `Song` モデル、`songs` API 契約、作成・更新フォームにはタグを保持・入出力する項目がなく、DB にも楽曲とタグを結び付ける関連テーブルがない。そのためタグは独立したマスタとしてしか扱えず、楽曲ごとのタグ選択・保存・取得・表示に利用できない

## Goal

楽曲モデルが複数の楽曲タグを保持できるようにし、管理 API の楽曲作成・更新・取得でタグ情報を一貫して入出力できる状態にする。既存の楽曲タグ CRUD はそのまま利用し、楽曲とタグの関連付けだけを追加する

## Scope

- `src/contracts/src/admin/songs` と生成物に、`Song` / `SongCreateRequest` / `SongUpdateRequest` のタグ項目を追加する
- `src/server/database/atlas/schemas` と `atlas.hcl` に、楽曲と楽曲タグを結び付ける関連テーブルを追加する
- `src/server/app/Models/Song` に関連 Eloquent Model と `Song` からの relation を追加する
- `src/server/packages/Song/Domain/Models` に、楽曲が保持するタグ ID 集合またはタグ参照の値オブジェクトを追加し、`Song` / `SongFactoryInterface` / `SongIntegrityService` に組み込む
- `src/server/packages/Song/Infrastructures/SongRepository.php` に、楽曲タグ関連の保存・削除・hydrate 処理を追加する
- `src/server/packages/Song/Application/Assemble` と `src/server/app/Http/Presenters/Api/Song` に、取得・作成・更新レスポンスへタグを含める変換を追加する
- `src/server/packages/Song/Application/UseCase/Create` / `Update`、`src/server/app/Http/Controllers/Api/Song` 周辺の入力データにタグ ID 配列を通す
- `src/admin/src/server/routes/songs.ts` と `src/admin/src/components/song/CreateForm.tsx` / `EditableForm.tsx` に、楽曲タグ一覧取得とタグ選択・送信を追加する
- 関連する feature / integration / unit test を更新し、楽曲作成・更新・取得時のタグ入出力を検証する

## Non-Scope

- 楽曲タグ CRUD API 自体の仕様変更
- 楽曲タグ名、表示順、検索条件など `song_tags` マスタ管理機能の追加・変更
- 閲覧サイト `src/viewer` でのタグ表示やタグ検索
- 楽曲検索 API のタグ絞り込み、タグ別一覧、タグの並び替え UI
- 既存クリエイター、楽曲種別、楽曲属性の仕様変更

## Acceptance Criteria

- 楽曲作成 API に複数の楽曲タグ ID を指定したとき、存在するタグだけを受け付け、作成後のレスポンスに指定したタグが `orderNo` 順または選択順として定義された順序で含まれる
- 存在しない楽曲タグ ID を楽曲作成・更新 API に指定したとき、楽曲は保存されず、検証可能なエラー応答になる
- 楽曲更新 API でタグ ID 配列を変更したとき、既存のタグ関連が差し替わり、削除したタグは取得レスポンスに残らず、追加したタグは取得レスポンスに含まれる
- 楽曲取得 API の `song` に、関連付いた楽曲タグの `songTagId`、`name`、`orderNo` が含まれる
- 楽曲保存時に、作詞者・作曲者・編曲者の既存保存処理は従来どおり維持され、タグ関連だけが追加で保存される
- 管理画面の楽曲作成・編集フォームで登録済み楽曲タグを選択でき、送信したタグ選択が API request に含まれる
- `src/contracts` の契約変更後、管理画面とサーバーの生成済み型・モデルが新しいタグ項目と一致している

## Steps

1. ✅ `src/contracts/src/admin/songs/domain.tsp` と `src/contracts/src/admin/songs/transport.tsp` を更新し、既存 `SongTag` 契約を参照するために `../song-tags` を import したうえで、`Song` に `tags: SongTag[]`、`SongCreateRequest` / `SongUpdateRequest` に `tags: RequestSongTag[]` を追加する。`RequestSongTag` は `PickProperties<SongTag, "songTagId">` とし、作詞者などの入力契約パターンに合わせる
2. ✅ 契約変更後に `mise run contract:compile:admin`、`mise run generate:client:admin`、`mise run generate:server` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`、`src/admin/src/generated/*`、`src/server/Generated/lib/Model/*Song*` の生成物を更新する。以降のサーバー・管理画面実装はこの生成型に合わせる
3. ✅ `src/server/database/atlas/schemas/song-taggings.my.hcl` を新規作成し、`song_id`、`song_tag_id`、`order_no` を持つ `song_taggings` テーブルを追加する。主キーは `[song_id, song_tag_id]`、外部キーは `songs.song_id` へ `CASCADE`、`song_tags.song_tag_id` へ `RESTRICT` とし、`src/server/database/atlas/atlas.hcl` の `table_schemas` に追加する
4. ✅ `src/server/app/Models/Song/SongTagging.php` を新規作成し、`src/server/app/Models/Song/Song.php` に `taggings()` relation と `$with` への追加を行う。`SongRepository` が既存の `lyricists` / `composers` / `arrangers` と同じ形で関連行を eager load できるようにする
5. ✅ `src/server/packages/Song/Domain/Models/Tags` 配下に `SongTagReference` と `SongTagReferences` を追加し、`Song`、`Song::reconstruct()`、`Song::toArray()`、`SongFactoryInterface`、`SongFactory`、`Tests\Support\Domain\EntityFactory::createSong()` にタグ集合を渡す引数を追加する。入力配列から `songTagId` と選択順 `orderNo` を作る実装は、既存 `Creators/*s::fromArray()` のパターンに合わせる
6. ✅ `src/server/packages/Song/Domain/Services/SongIntegrityService.php` に `SongTagRepositoryInterface` を注入し、`prepareForCreate()` / `prepareForUpdate()` / `build()` にタグ入力を通す。`SongTagReferences::fromArray()` のバリデーションと `SongTagRepositoryInterface::findByIds()` による存在確認を追加し、存在しないタグ ID は保存前に `BusinessRuleViolationError` または `DomainValidationError` として返す。必要に応じて `SongTagRepositoryInterface` / `SongTagRepository` に `findByIds(SongTagId ...$ids): array` を追加する
7. ✅ `src/server/packages/Song/Infrastructures/SongRepository.php` を更新し、保存時に `SongTagging` を既存関連と同じトランザクション内で全削除・再挿入する。`hydrate()` では `song_taggings.order_no` 順に `songTagId` と `orderNo` を復元し、作詞者・作曲者・編曲者の保存処理は既存のまま維持する
8. ✅ `src/server/packages/Song/Application/UseCase/Create/CreateInputData.php`、`Update/UpdateInputData.php`、`CreateInteractor.php`、`UpdateInteractor.php`、`Application/Assemble`、`app/Http/Presenters/Api/Song/Converter.php` を更新し、入力タグ ID 配列をドメインへ渡し、レスポンスでは `songTagId`、`name`、`orderNo` を持つ `SongTag` 配列を返す。Assembler では `SongTagRepositoryInterface::findByIds()` を使い、レスポンス順は `song_taggings.order_no` の選択順を維持する
9. ✅ `src/admin/src/server/routes/songs.ts` で `/songs/create-form` と `/:songId/edit-form` が `songTagServiceListSongTags` も取得して返すようにし、POST / PUT の body schema と転送 body に `tags` を追加する。`src/admin/src/components/song/CreateForm.tsx` と `EditableForm.tsx` には楽曲タグ選択用の状態と `SearchableSelect` ベースの追加・削除 UI を追加し、送信時に `{ songTagId }[]` を含める
10. ✅ `src/server/tests/Feature/Api/Song/CreateSongTest.php`、`UpdateSongTest.php`、`GetSongTest.php`、関連する Integration / Unit テストを更新し、タグ付き作成、存在しないタグ ID の作成・更新失敗、更新時の差し替え、取得レスポンスの `tags` 内容を検証する。必要に応じて `SongTagRepositoryTest` に `findByIds()` の順序・件数検証を追加する

## Decision Log

- 2026-05-01: 楽曲タグ入力は作詞者・作曲者・編曲者と同じ `{ songTagId }[]` の配列契約にし、関連テーブル側の `order_no` で選択順を保存する。これによりタグマスタの `orderNo` と楽曲ごとの選択順を混同せず、Acceptance Criteria の「選択順」を明確に満たせる
- 2026-05-01: 関連テーブル名は既存 dump にも見える `song_taggings` を採用し、Atlas スキーマを Source of Truth として新規追加する。既存の `song_lyricists` / `song_composers` / `song_arrangers` と同じ複合主キー、`songs` 側 `CASCADE`、タグマスタ側 `RESTRICT` の制約に揃える
- 2026-05-01: `Song` ドメインにはタグ ID 参照の集合を持たせ、タグ名は Assembler で `SongTagRepositoryInterface` から解決する。タグ名の重複保持を避け、既存のクリエイター名解決パターンに合わせる
- 2026-05-01: 存在しないタグ ID の検証は Repository 保存時の外部キーエラーに任せず、`SongIntegrityService` で保存前に検出する。API が検証可能なエラー応答を返し、楽曲本体と既存関連が部分保存されないようにするため
- 2026-05-01: タグ関連の保存は既存クリエイター関連と同じ全差し替え方式にする。更新時の削除漏れを避けつつ、既存保存処理への変更を最小限にするため

## Validation

- `mise run contract:compile:admin` を実行し、`Song`、`SongCreateRequest`、`SongUpdateRequest`、`RequestSongTag` が OpenAPI に出力されることを確認する
- `mise run generate:client:admin` と `mise run generate:server` を実行し、管理画面 TypeScript 型とサーバー OpenAPI モデルが `tags` 項目を持つことを確認する
- `mise run migrate` を local / testing 環境へ適用し、`song_taggings` の主キー、外部キー、`order_no` が Atlas 定義どおり作成されることを確認する
- `docker compose exec php php artisan test tests/Feature/Api/Song/CreateSongTest.php tests/Feature/Api/Song/UpdateSongTest.php tests/Feature/Api/Song/GetSongTest.php` を実行し、タグ付き作成、更新差し替え、取得レスポンス、存在しないタグ ID の失敗を確認する
- `docker compose exec php php artisan test tests/Integration/Song tests/Unit/Song` を実行し、ドメイン集合、IntegrityService、Repository、Assembler のタグ追加による既存挙動の破壊がないことを確認する
- `bunx tsc --noEmit` を実行し、`src/admin/src/server/routes/songs.ts`、`CreateForm.tsx`、`EditableForm.tsx` が生成型と一致していることを確認する
- `mise run phpstan` と `mise run ecs` を実行し、PHP の型・スタイル検査を通す
