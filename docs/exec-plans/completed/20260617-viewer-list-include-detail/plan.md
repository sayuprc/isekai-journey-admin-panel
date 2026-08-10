# Execution Plan — Viewer 一覧 API への詳細データ統合

実装計画(どう進めるか)を書く。問題定義は同ディレクトリの `issue.md` を参照する

## Title

Viewer 一覧 API への詳細データ統合

## Status

completed

## Steps

1. ✅ `src/contracts/src/viewer/songs/{domain.tsp,service.tsp,transport.tsp}` と `src/contracts/src/viewer/media/{domain.tsp,service.tsp,transport.tsp}` を変更する。`SongService.getSong` / `MediaService.getMedia` と `SongDetailResponse` / `MediaDetailResponse` を削除し、`SongListItem` に現在の `SongDetail.media` 相当の公開 media summary 配列を追加する。`MediaListItem` には現在の `MediaDetail.counts` と `MediaDetail.songs` 相当の公開 song summary 配列を追加する。summary model は一覧から参照される名前に寄せ、examples も新 response shape に更新する
2. ✅ Step 1 に依存して生成物を更新する。`mise run contract:format:check` と `mise run contract:compile:viewer` で `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` から `/songs/{songId}` と `/media/{mediaId}` を消し、`GET /songs` / `GET /media` の item schema に詳細相当フィールドが出ることを確認する。続けて `mise run viewer:generate` を実行し、`src/viewer/src/generated/{sdk.gen.ts,types.gen.ts,index.ts}` から `songServiceGetSong` / `mediaServiceGetMedia` と detail response 型を消す。`src/server/Generated/` は現行 `api:generate` が Admin OAS 入力であるため手動編集せず、必要なら `git diff -- src/server/Generated` で Viewer 由来の差分が出ないことを確認する
3. ✅ Step 1 の shape に合わせて Viewer 用 query DTO と query service を更新する。`src/server/packages/Song/Application/Viewer/Query/SongListItem.php` に `array<SongMediaSummary>` 相当の `media` を追加し、`SongDetail.php` は削除または `SongListItem` へ統合する。`SongDetailMediaSummary.php` は一覧用 summary 名に改名するか、一覧 DTO から直接参照する。`src/server/packages/Song/Infrastructures/Viewer/SongQueryService.php` の `list()` に `get()` 側の公開 `songMediaLinks.media` eager load を移し、`mediaCount` は返却する公開 media 配列の件数と一致させる。`get()` は削除する。Media 側も同様に `MediaListItem.php` に `array<MediaSongSummary>` 相当の `songs` を追加し、`MediaQueryService.php` の `list()` に公開 `songMediaLinks.song` eager load を移し、`songCount` は公開 songs 配列の件数から返す。`MediaDetail.php` と `get()` は削除する
4. ✅ Step 3 に依存して Viewer 詳細 API 専用の application / HTTP 公開口を削除する。`src/server/packages/{Song,Media}/Application/Viewer/Query/{SongQueryServiceInterface.php,MediaQueryServiceInterface.php}` から `get()` を削除し、`src/server/packages/{Song,Media}/Application/Viewer/UseCase/Get/` を削除する。`src/server/app/Http/Controllers/Api/Viewer/V1/{Song,Media}/Get*Controller.php` と `src/server/app/Http/Presenters/Api/Viewer/V1/{Song,Media}/GetPresenter.php` を削除する。`src/server/routes/viewer.php` から `GET /v1/songs/{songId}` / `GET /v1/media/{mediaId}` と import を削除し、`src/server/packages/{Song,Media}/Route/Viewer*RouteMap.php` から `Get` case を削除する
5. ✅ Step 3 に依存して一覧 presenter を新 shape に更新する。`src/server/app/Http/Presenters/Api/Viewer/V1/Song/ListPresenter.php` に media summary の配列変換を追加し、`counts.mediaCount` は `$song->media` の件数から返す。`src/server/app/Http/Presenters/Api/Viewer/V1/Media/ListPresenter.php` に `counts.songCount` と songs summary の配列変換を追加し、非公開 song が含まれないことを query service の結果に沿って返す。既存の field 名、date format、enum の `{name,value}` 形式は詳細 presenter と同じにする
6. ✅ Step 4 と Step 5 に依存して Feature テストを整理する。`src/server/tests/Feature/Api/Viewer/V1/Song/ListSongTest.php` に、詳細 API テストで検証していた公開 media summary の並び順、非公開 media 除外、`counts.mediaCount` と `media` 件数一致の期待値を移す。`src/server/tests/Feature/Api/Viewer/V1/Media/ListMediaTest.php` に、公開 song summary の並び順、非公開 song 除外、`counts.songCount` と `songs` 件数一致の期待値を移す。`src/server/tests/Feature/Api/Viewer/V1/{Song/GetSongTest.php,Media/GetMediaTest.php}` は route 削除に合わせて削除する
7. ✅ Step 2 に依存して Viewer 側 repository と型 alias を更新する。`src/viewer/src/features/songs/api.ts` から `songServiceGetSong` import、`get(songId)`、repository の `get` export を削除し、`all()` だけを残す。`src/viewer/src/features/media/api.ts` も `mediaServiceGetMedia` と `get(mediaId)` を削除する。`src/viewer/src/features/{songs,media}/types.ts` は `SongDetail = SongListItem`、`MediaDetail = MediaListItem` 相当の alias に変更し、summary 型は生成後の一覧用 summary 型名へ追従する
8. ✅ Step 7 に依存して Viewer 詳細ページの SSG を一覧データだけに変更する。`src/viewer/src/pages/songs/[songId].astro` と `src/viewer/src/pages/fragments/songs/[songId].astro` は `Promise.all(...songRepository.get(...))` をやめ、`(await songRepository.all()).map(song => ({ params: { songId: song.songId }, props: { song } }))` にする。`src/viewer/src/pages/media/[mediaId].astro` と `src/viewer/src/pages/fragments/media/[mediaId].astro` も同様に `props: { media }` を直接渡す。`SongDetailContent.astro` / `MediaDetailContent.astro` は型 alias 変更で通る想定だが、生成型名変更で import が合わない場合のみ最小修正する
9. ✅ 全体の参照漏れを確認して仕上げる。`rg "songServiceGetSong|mediaServiceGetMedia|ViewerSongRouteMap::Get|ViewerMediaRouteMap::Get|/songs/\\{songId\\}|/media/\\{mediaId\\}|songRepository\\.get|mediaRepository\\.get" src/contracts src/server src/viewer` で Viewer 詳細 API 由来の参照が残っていないことを確認し、必要な formatter / static check / test を実行する

## Decision Log

- 2026-06-17: 一覧 item を詳細相当の唯一の Viewer 表示用 DTO にする。Viewer SSG が全詳細ページを一覧データだけで生成するには、詳細 DTO を残して別口で取得するより、`SongListItem` / `MediaListItem` を詳細コンポーネントへそのまま渡せる形にする方が呼び出し経路と型が単純になる
- 2026-06-17: `counts.mediaCount` と `counts.songCount` は別集計値ではなく、返却する公開 summary 配列の件数から導く。Acceptance Criteria が「返却される公開関連データの件数と一致」を求めているため、`withCount` と eager load の条件ずれを避ける
- 2026-06-17: 詳細 API の NotFound 挙動は一覧 API 統合後の公開口から削除する。非公開 / 存在しない ID の個別取得 route 自体を Viewer から消すため、検証対象は一覧で非公開 song / media が含まれないことに移す
- 2026-06-17: `src/server/Generated/` は Viewer API の PHP 生成物としては使われておらず、現行 `mise run api:generate` も `ADMIN_OAS_FILE` を入力にしている。Viewer API のサーバー側検証は `ViewerOpenApiValidator` が `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` を直接読むため、このタスクでは `src/server/Generated/` を手動編集しない。Admin API の `getSong` / `getMedia` 生成物は Non-Scope として残す
- 2026-06-17: Viewer repository から `get()` を削除し、詳細ページは `all()` の結果を props に渡す。`get()` を一覧内検索ヘルパーとして残すと「詳細 API 呼び出しがない」ことの検証が曖昧になるため、呼び出し口自体を消す

## Validation

- Contract: `mise run contract:format:check` と `mise run contract:compile:viewer` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `/songs/{songId}` と `/media/{mediaId}` が存在しないこと、`SongListItem` に `media`、`MediaListItem` に `counts` と `songs` が出力されることを確認する
- Viewer generated client: `mise run viewer:generate` を実行し、`src/viewer/src/generated/sdk.gen.ts` と `index.ts` から `songServiceGetSong` / `mediaServiceGetMedia` が消え、`types.gen.ts` の一覧 item 型が詳細ページに必要な field を持つことを確認する
- Server behavior: `mise run api:test src/server/tests/Feature/Api/Viewer/V1/Song/ListSongTest.php src/server/tests/Feature/Api/Viewer/V1/Media/ListMediaTest.php` を実行し、一覧 API が詳細相当の関連 summary を返すこと、非公開関連データを除外すること、counts と配列件数が一致することを確認する
- Server static checks: `mise run api:phpstan packages/Song packages/Media app/Http/Controllers/Api/Viewer/V1 app/Http/Presenters/Api/Viewer/V1 tests/Feature/Api/Viewer/V1` と `mise run api:ecs packages/Song packages/Media app/Http/Controllers/Api/Viewer/V1 app/Http/Presenters/Api/Viewer/V1 tests/Feature/Api/Viewer/V1` を実行する
- Viewer checks: `mise run viewer:check` と `cd src/viewer && bun run build` を実行し、一覧ページ、通常詳細ページ、fragment 詳細ページが generated list 型だけでビルドできることを確認する
- 差分確認: `rg "songServiceGetSong|mediaServiceGetMedia|songRepository\\.get|mediaRepository\\.get|ViewerSongRouteMap::Get|ViewerMediaRouteMap::Get" src/contracts src/server src/viewer` で Viewer 詳細 API の参照が残っていないことを確認する。`git diff -- src/server/Generated` で Viewer OAS 由来の不要な生成差分がないことも確認する
