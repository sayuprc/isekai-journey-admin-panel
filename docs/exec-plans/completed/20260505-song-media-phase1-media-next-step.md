## Title

Song Media Phase1 Media Next Step

## Status

completed

## Background

`Media` のドメインモデル、Eloquent モデル、`song_media_links` スキーマはすでに存在する。一方で、Phase 1 の要求である「Song から公式 Media を登録・関連付け・表示順管理する流れ」は、`Song` の API 契約、アプリケーションユースケース、assembler、admin BFF/UI にまだ接続されていない。

## Goal

Song 管理フローに `Media` を接続する次ステップを定義する。`Song` 作成・更新・取得で Media 関連を扱うために、どの契約・サーバー・admin 画面を変更対象にするかを明確にする。

## Scope

- `src/contracts/src/admin/songs/domain.tsp`, `transport.tsp`, `service.tsp`
- `src/server/packages/Song/Domain/Models/Song.php`, `Domain/Services/SongIntegrityService.php`
- `src/server/packages/Song/Application/UseCase/Create`, `Get`, `Update`
- `src/server/packages/Song/Application/Assemble/SongAssembler.php`, `AssembledSong.php`
- `src/server/packages/Song/Infrastructures/SongRepository.php`
- `src/server/app/Http/Presenters/Api/Song/Converter.php`
- `src/admin/src/server/routes/songs.ts`
- `src/admin/src/components/song/CreateForm.tsx`, `EditableForm.tsx`

## Non-Scope

- `Media` 単独 CRUD / 一覧画面の新設
- `viewer` 側への表示反映
- `Event` や `Release` との接続
- 画像メディアや非公式メディア対応

## Acceptance Criteria

- 現状調査として、`src/server/packages/Media`、`src/server/app/Models/Media/Media.php`、`src/server/database/atlas/schemas/song-media-links.my.hcl` の存在と役割が文書内で整理されている
- `Song` 側の未接続箇所として、契約、Create/Get/Update の input/output、assembler、repository、admin BFF/UI の対象ファイルが列挙されている
- 実装スコープと非スコープを読めば、次の Plan フェーズで `Steps` を分解できる粒度になっている

## Steps

1. `Song` 契約へ Media 入出力を追加する ✅
   `src/contracts/src/admin/songs/domain.tsp` に `SongMediaType`、`SongLinkedMedia`、`RequestSongMediaLink` を追加し、`Song` に `media` 配列を持たせる。`src/contracts/src/admin/songs/transport.tsp` の `SongCreateRequest` / `SongUpdateRequest` と `SongGetResponse` / `SongCreateResponse` / `SongUpdateResponse` が Media 配列を通せる形へ更新する。`src/contracts/src/admin/songs/service.tsp` は API パスを変えず request / response 契約のみ更新する。

2. Song フォーム内の「新規 Media 作成の受け口」に必要な最小契約を追加する ✅
   `Media` 単独 CRUD は作らず、`src/contracts/src/admin/media/domain.tsp`、`transport.tsp`、`service.tsp` を新規追加して「Song フォームから呼ぶ既存 Media 検索 / 作成」の最小 API だけ定義する。`src/contracts/src/admin/main.tsp` に `./media` を追加し、生成物は `mise run generate` で更新する前提にする。

3. `Song` ドメインで Media 関連を保持・検証できるようにする ✅
   `src/server/packages/Song/Domain/Models/Song.php` に Media コレクションを追加し、`reconstruct()` / `toArray()` の入出力を拡張する。`src/server/packages/Song/Domain/Models/Media/SongMediaLinks.php` を新規追加して重複 Media、`song_media_type`、`order_no` を検証し、`src/server/packages/Song/Domain/Services/SongIntegrityService.php` で `Media` 存在確認と create / update 両経路のバリデーションへ組み込む。存在確認用に `src/server/packages/Media/Domain/Models/MediaRepositoryInterface.php` と `src/server/packages/Media/Infrastructures/MediaRepository.php` を追加する。

4. `Song` の Create / Get / Update ユースケースで Media を通す ✅
   `src/server/packages/Song/Application/UseCase/Create/CreateInputData.php`、`Get/GetOutputData.php`、`Update/UpdateInputData.php` などの input / output に Media 配列を追加し、`CreateUseCase.php` と `UpdateUseCase.php` が `SongIntegrityService` へ Media を渡すようにする。`GetUseCase.php` は assembler が返す Media をそのまま返せる状態に揃える。

5. repository / assembler / presenter を Media 対応に揃える ✅
   `src/server/packages/Song/Infrastructures/SongRepository.php` で `song_media_links` の delete / insert / hydrate を追加し、`App\Models\Song\Song` の `songMediaLinks` relation を eager load 対象に含める。`src/server/packages/Song/Application/Assemble/AssembledSong.php` に assembled media を追加し、`SongAssembler.php` で `MediaRepositoryInterface` を使って `media_id` からタイトル / URL / type / display 情報を解決する。`src/server/app/Http/Presenters/Api/Song/Converter.php` では OpenAPI の `Song` へ Media 配列をマッピングする。

6. Song フォーム用の admin BFF を Media データ込みに拡張する ✅
   `src/admin/src/server/routes/songs.ts` の `/create-form` と `/:songId/edit-form` が既存 Media 候補を返し、`POST /songs` と `PUT /songs/:songId` が Media 配列を API へ中継するよう更新する。新規 Media 作成の受け口は `src/admin/src/server/routes/media.ts` を追加して Song フォームから呼べる最小 `POST` / 検索 API を BFF 経由で公開する。

7. admin の Song 作成 / 編集フォームに Media セクションを追加する ✅
   `src/admin/src/components/song/CreateForm.tsx` と `EditableForm.tsx` に「既存 Media の検索追加」「`song_media_type` の選択」「並び順変更」「削除」を行う UI を追加する。重複ロジックは `src/admin/src/components/song/MediaSection.tsx` のような共通コンポーネントへ切り出し、同じセクション内に新規 Media 作成フォームまたはダイアログを置いて、作成成功後に候補一覧へ即反映できるようにする。

8. 生成物更新とテストを通して end-to-end を確認する ✅
   `src/contracts` 起点の生成物、`src/server/Generated`、`src/admin/src/generated` を更新したうえで、Song の create / get / update で Media が round-trip すること、admin で関連付け順が保持されること、inline Media 作成後に Song へ追加できることを確認する。

## Decision Log

- 既存 `Media` の基盤は利用する。`src/server/packages/Media` は domain model 群、`src/server/app/Models/Media/Media.php` は Eloquent 永続化モデル、`src/server/database/atlas/schemas/song-media-links.my.hcl` は Song と Media の関連と `song_media_type` / `order_no` を保持するスキーマであり、新規テーブル追加は不要とする。
- Phase 1 の主軸はあくまで `Song` 作成・更新・取得に Media を通すことに限定する。`Media` 一覧画面、詳細画面、編集画面、viewer 反映はこの計画に含めない。
- admin 側の「新規 Media 作成の受け口」は Song フォーム内から呼ぶ最小 API と UI に留める。Media 単独 CRUD を先に広げるより、Song からの関連付け完結を優先する。
- `song_media_links` には `song_media_type` と `order_no` があるため、Song request でも Media ごとに `mediaId` だけでなく `songMediaType` / `orderNo` を明示的に送る前提にする。
- `Song` assembler / presenter で返す Media は、関連 ID だけでなく admin 画面再表示に必要なタイトル、URL、Media 種別、表示フラグまで含める。これにより edit form で再検索なしに初期表示できる。

## Validation

- ✅ `mise run contract:test`
- ✅ `mise run contract:compile:admin`
- ✅ `mise run admin:generate`
- ✅ `mise run api:generate`
- ✅ `docker compose exec php php artisan test tests/Feature/Api/Song tests/Feature/Api/Media tests/Unit/Song/Domain/Services/SongIntegrityServiceTest.php`
- ✅ `cd src/admin && bun run lint:check src/components/song src/server/routes src/server/index.ts`
- ✅ `cd src/admin && bunx tsc --noEmit -p tsconfig.json`
- 未実施 `cd src && bun --filter admin build`
- 未実施 管理画面での手動確認
