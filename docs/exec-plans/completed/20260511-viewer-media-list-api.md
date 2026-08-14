# Execution Plan

## Title

Viewer 向けメディア一覧 API

## Status

completed

## Background

Viewer の楽曲側は `src/contracts/src/viewer/songs/*`、`src/server/routes/viewer.php`、`src/server/app/Http/Controllers/Api/Viewer/V1/Song/ListSongController.php`、`src/viewer/src/features/songs/api.ts` まで実装が進み、`docs/exec-plans/completed/20260507-viewer-song-list-api-contracts.md` と `docs/exec-plans/completed/20260511-viewer-song-detail-api.md` の順で「先に一覧 API、後から詳細 API」というパターンが確立されている。一方でメディア側は viewer 公開 API がまだ存在せず、`src/viewer/src/pages/media/[mediaId].astro` の `getStaticPaths` と `src/viewer/src/pages/media/_index.astro` がいずれも `src/viewer/src/data/mock/site-data` 経由のダミーデータに依存しており、メディア詳細ページにつなぎ込む手前で一覧取得の手段が無い。`src/viewer/src/features/media/` には `labels.ts` と `sort.ts` しか存在せず、`api.ts` / `types.ts` が未整備である。サーバー側にも `Media` パッケージは存在するが、`src/server/packages/Media/Application/Admin/` 配下に Admin 向けユースケースしか定義されておらず、`src/server/packages/Media/Route/MediaRouteMap.php` に viewer 用 case が無い。`src/contracts/src/viewer/main.tsp` も songs のみを import しており、viewer media contract は未定義である。メディア詳細 API へ進む前に、楽曲側と同じ順序でまず viewer 向けメディア一覧 API の境界を定義・実装し、`getStaticPaths` を実 API から駆動できる状態を作る必要がある

## Goal

Viewer から公開メディアの一覧を取得できる API 境界(contract / route / use case / query / controller / presenter)を整備し、`src/viewer` 側に `mediaRepository.all()` 相当の取得経路を用意して `src/viewer/src/pages/media/[mediaId].astro` の `getStaticPaths` を mock から実 API ベースへ差し替え可能な状態にする

## Scope

- `src/contracts/src/viewer/media/` を新設し、`domain.tsp` / `service.tsp` / `transport.tsp` / `main.tsp` で viewer 向け `GET /media` の一覧 contract を定義する
- `src/contracts/src/viewer/main.tsp` に viewer media モジュールの import を追加し、`mise run contract:compile:viewer` 経由で `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に反映する
- `src/server/packages/Media/Route/MediaRouteMap.php` の刷新もしくは `Media/Route/ViewerMediaRouteMap.php` 新設により viewer 用一覧 route 名を定義する
- `src/server/routes/viewer.php` に `GET /v1/media` の route を追加する
- `src/server/packages/Media/Application/Viewer/Query/` と `src/server/packages/Media/Application/Viewer/UseCase/Search/` (または `List/`) に viewer 一覧用 DTO・query interface・use case を新設する
- `src/server/packages/Media/Infrastructures/Viewer/MediaQueryService.php` (または同等の Viewer 向け実装クラス)を新設し、`media.is_display = true` のみを返す
- `src/server/app/Http/Controllers/Api/Viewer/V1/Media/ListMediaController.php` と `src/server/app/Http/Presenters/Api/Viewer/V1/Media/ListPresenter.php` を新設する
- `src/server/tests/Feature/Api/Viewer/V1/Media/` に一覧取得 feature test を追加する
- `src/viewer/src/features/media/api.ts` と `src/viewer/src/features/media/types.ts` を新設し、generated client 経由の `mediaRepository.all()` を提供する
- `src/viewer/src/pages/media/[mediaId].astro` の `getStaticPaths` を `mediaRepository.all()` 由来に差し替える(ページ本体や `MediaDetailContent.astro` の本格的な API 化は次タスクへ送る)

## Non-Scope

- Viewer 向けメディア詳細 API(`GET /media/{mediaId}`)の contract / 実装。これは本タスク完了後に別 exec-plan で扱う
- `src/viewer/src/pages/media/_index.astro` および `MediaDetailContent.astro` の mock データ依存を完全に剥がす作業(詳細 API 着地後にまとめて扱う)
- `src/viewer/src/data/mock/site-data` 自体の削除や、`SITE_DATA.media` を参照する他ページ・コンポーネントの差し替え
- `src/contracts/src/admin/media/*` および Admin 向け `GET /media` / `GET /media/{mediaId}` 等の互換変更
- Admin 側の `MediaRouteMap` / use case / controller / presenter の挙動変更(既存 case は破壊しない)
- 楽曲との関連表示(`Q.songsOfMedia` 相当)を返すための contract / 集計実装
- メディア詳細用のサムネイル・色情報・プラットフォーム別装飾などのデザイン決定

## Acceptance Criteria

- `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `GET /media` と一覧レスポンス schema が出力され、少なくとも `mediaId` / `title` / `type` / `publishedAt` / `url` を表現できる
- `mise run contract:compile:viewer` と `mise run contract:format:check` が成功する
- `GET /v1/media` がサーバーで配線され、`media.is_display = true` のメディアのみを返す
- `src/server/tests/Feature/Api/Viewer/V1/Media/` の feature test で「公開メディアが含まれる」「非公開メディアが除外される」「ページング/cursor 仕様が contract と一致する」が固定 JSON で検証されている
- `src/viewer/src/features/media/api.ts` に `mediaRepository.all()` が存在し、`src/viewer/src/features/songs/api.ts` と同様に `nextCursor` を消費して全件取得できる
- `src/viewer/src/pages/media/[mediaId].astro` の `getStaticPaths` が `src/viewer/src/data/mock/site-data` 由来から `mediaRepository.all()` 由来に置き換わり、ビルドが通る
- Admin 側 `GET /media` 系エンドポイントの挙動・契約に差分が無い

## Steps

1. ✅ `src/contracts/src/viewer/media/` を新設し、`domain.tsp` に `mediaId` / `title` / `MediaType`(既存 `viewer/songs/domain.tsp` の `MediaTypeValue` と整合させた enum: video=1, article=2, socialPost=3, officialPage=4, other=99)/ `MediaFormat`(mv=1, audioVideo=2, streamArchive=3, shortVideo=4, liveClip=5, other=99。`Media\Domain\Models\MediaFormat` に揃える)/ `publishedAt`(`@format("date")`)/ `url`(`@format("uri")`)と `MediaListItem` モデルを定義する。命名は楽曲側の `SongListItem` と完全に対応させる
2. ✅ `src/contracts/src/viewer/media/transport.tsp` に `MediaListResponse { media: MediaListItem[]; nextCursor?: cursor; }` を定義する。`cursor` / `limit` は viewer/songs/transport.tsp で定義済みの scalar を再利用する(同一 namespace `IsekaiObservatory.Viewer` のため新規定義不要)。`@example` も `viewer/songs/transport.tsp` を踏襲
3. ✅ `src/contracts/src/viewer/media/service.tsp` に `@tag("Media") @route("/media") interface MediaService { listMedia(@query cursor?, @query limit?): Ok<MediaListResponse> | InternalServerError; }` を追加する。楽曲側が `listSongs` なので命名は `listMedia`(List 系で統一、Search にしない)
4. ✅ `src/contracts/src/viewer/media/main.tsp` を新設し domain/service/transport を import。`src/contracts/src/viewer/main.tsp` に `import "./media";` を追加
5. ✅ `mise run contract:format:check` → 失敗時は `cd src && bun --filter contracts format` で整形。続けて `mise run contract:compile:viewer` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `GET /media` / `MediaListResponse` / `MediaListItem` が出力されることを確認する
6. ✅ `src/server/packages/Media/Route/MediaRouteMap.php` は Admin 既存 case を保持したまま、`src/server/packages/Media/Route/ViewerMediaRouteMap.php` を新設し `case List = 'viewer.media.list';` を定義する(Song 側の `SongRouteMap` / `ViewerSongRouteMap` 並列パターンに合わせる)
7. ✅ `src/server/packages/Media/Application/Viewer/Query/` を新設し、`MediaQueryServiceInterface`(`list(?string $cursor, int $limit): MediaListPage`)、`MediaListItem`(`mediaId, title, url, publishedAt (DateTimeImmutable), MediaType, MediaFormat, orderNo or publishedAt+mediaId` の seek key 用フィールド)、`MediaListPage`、`MediaListCursor`(`Song\Application\Viewer\Query\SongListCursor` と同じ base64+json 方式。`media` テーブルには `order_no` が無いと予想されるため、cursor キーは `publishedAt`(`Y-m-d` 文字列)+ `mediaId` の複合とする)、`DecodedMediaListCursor` を定義する
8. ✅ `src/server/packages/Media/Application/Viewer/UseCase/List/` を新設し、`ListInputData`(cursor, limit nullable)、`ListOutputData`(media array, nextCursor)、`ListUseCase`(`SongQueryService` 同様 DEFAULT_LIMIT/MAX_LIMIT=50、`Ok` で包む)を実装する。`UseCaseAuthorizer` は viewer 公開 API なので不要(楽曲側に揃える)
9. ✅ `src/server/packages/Media/Infrastructures/Viewer/MediaQueryService.php` を新設し `App\Models\Media\Media`(Eloquent モデル；既存 Admin 実装の参照名を確認しつつ流用)に対し `where('is_display', true)->orderBy('published_at', 'desc')->orderBy('media_id')->limit($limit + 1)` し、cursor 指定時は `(published_at, media_id)` の seek 条件(楽曲側の `order_no, song_id` と対応)で次ページを取得する。`+1` フェッチで次ページ判定し、はみ出し分を切って `nextCursor` を生成
10. ✅ `src/server/app/Http/Controllers/Api/Viewer/V1/Media/ListMediaController.php` と `src/server/app/Http/Presenters/Api/Viewer/V1/Media/ListPresenter.php` を新設し、Song の `ListSongController` / `ListPresenter` をそのままパターンとする。Presenter の `toArray` では `mediaId, title, url, publishedAt (Y-m-d 文字列), type:{name,value}, format:{name,value}` を返す。`src/server/routes/viewer.php` に `Route::prefix('media')->group(...)` を追加し、`ListMediaController` を `ViewerMediaRouteMap::List` で配線する。`MediaQueryServiceInterface` の DI bind を `app/Providers/AppServiceProvider.php` などにある Song 側 bind と同じ場所に追加する
11. ✅ `src/server/tests/Feature/Api/Viewer/V1/Media/ListMediaTest.php` を新設し、`Tests\Support\DatabaseTestCase` + `EntityFactory` + `EntityStore` で公開 / 非公開メディアを混在させ、`limit=1` でページングし `nextCursor` を消費して 2 ページ目を取り切ることを `assertExactJson` で固定検証する。`ListSongTest` を雛形にする
12. ✅ `src/viewer/src/features/media/types.ts` を新設(`Media = generated.MediaListItem`)、`src/viewer/src/features/media/api.ts` を新設し、`mediaServiceListMedia` を `cursor` ループで叩く `all()` を `songRepository.all` と同じ構造で実装、`mediaRepository = { all }` を export する
13. ✅ `src/viewer/src/pages/media/[mediaId].astro` の `getStaticPaths` を `await mediaRepository.all()` 由来に差し替え、各 entry を `params: { mediaId: m.mediaId }` で展開する。`props.entry` は当面 mock に依存しているコンポーネントとの整合のため、`SITE_DATA.media` の同一 id があれば渡し、無ければ最小プレースホルダ(title だけ実 API 値で埋め、他はダミー)を渡す。これは詳細 API 着地までの暫定で、Non-Scope に該当することを Decision Log に明記する
14. ✅ 検証(Validation セクション参照)を順次実施し、Admin の `GET /media` 系に差分が無いことを `mise run api:test --filter Admin/V1/Media` 相当で確認する

## Decision Log

- 2026-05-11: viewer 公開 API のインターフェース名は楽曲側 (`SongService.listSongs`) に揃え、`MediaService.listMedia` とする。Admin 側は `Search` だが、viewer は SSG での全件取得を想定しているため `List` 命名に統一する。ディレクトリも `Application/Viewer/UseCase/List/` で固定する
- 2026-05-11: cursor 仕様は楽曲側 `SongListCursor` の base64(json) を踏襲。ただし `media` テーブルには楽曲のような `order_no` が無いため、cursor の安定キーは `(published_at, media_id)` 複合とし、降順並びでもキーが一意になるようにする。これにより重複・取りこぼしを避ける
- 2026-05-11: `is_display = true` フィルタは Infrastructures 層 (`MediaQueryService`) で適用する。理由は (1) 楽曲側 `Song\Infrastructures\Viewer\SongQueryService` がそうしているから、(2) UseCase は契約上 viewer = 公開のみ取得という前提で組み、Domain criteria を経由しなくて済むから。Domain の `MediaRepositoryInterface` には触らない(Admin 側に影響を与えない)
- 2026-05-11: viewer 用の query service クラス名は `Media\Infrastructures\Viewer\MediaQueryService` とする。Admin 側 `MediaDetailQueryService.php` とはディレクトリで分離。インターフェースは `Media\Application\Viewer\Query\MediaQueryServiceInterface`。Song 側 `Song\Infrastructures\Viewer\SongQueryService` と完全対称の構造にする
- 2026-05-11: Route enum は Admin の `MediaRouteMap` を温存し、`ViewerMediaRouteMap` を新設する。Song 側 (`SongRouteMap` / `ViewerSongRouteMap`) と並列構造になり、Admin 用 case 削除や rename による回帰を回避できる
- 2026-05-11: `getStaticPaths` での全件取得は `mediaRepository.all()` を `await` する非同期 `getStaticPaths` とし、内部で `nextCursor` ループにより全公開メディアを取り切る。詳細ページ本体は本タスクのスコープ外(Non-Scope 参照)のため、`props.entry` には現状 `SITE_DATA.media` の同一 id 値があればそれを渡し、無ければ最小プレースホルダで埋める暫定実装とする。詳細 API 着地後にまとめて剥がす
- 2026-05-11: viewer contract の `MediaListItem` には Admin contract に存在する `isDisplay` を含めない。viewer は公開済みのみ返す前提が API 契約に内包される(楽曲側の方針と同じ)

## Validation

- `mise run contract:format:check`
- `mise run contract:compile:viewer` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `GET /media`, `MediaListResponse`, `MediaListItem`(`mediaId` / `title` / `type` / `publishedAt` / `url` を含む)が出力されていることを確認する
- `mise run api:ecs`, `mise run api:phpstan`, `mise run api:arkitect` をパスする
- `mise run api:test --filter Viewer/V1/Media` 相当で `ListMediaTest` がグリーン。テストでは「公開メディアのみ返ること」「非公開メディアが除外されること」「`limit=1` で 1 件目に `nextCursor` が付与され、その cursor で 2 ページ目が固定 JSON で得られること」を `assertExactJson` で検証する
- `mise run api:test --filter Admin/V1/Media` 相当で Admin 側 `GET /media` 系テストが既存通りグリーンであることを確認し、契約差分が無いことを担保する
- `cd src && bun --filter viewer build`(または mise の viewer build タスク)で SSG ビルドが通り、`getStaticPaths` が `mediaRepository.all()` 由来で全公開メディア分のルートを生成することを確認する
- 生成された OAS の `MediaListItem.type` / `format` が enum 値で表現され、`viewer/songs/domain.tsp` の `MediaTypeValue` と整合していることを目視確認する
