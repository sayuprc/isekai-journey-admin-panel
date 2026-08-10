# Execution Plan

## Title

Viewer 向けメディア詳細取得 API

## Status

completed

## Background

Viewer のメディア一覧 API は `src/contracts/src/viewer/media/{service,domain,transport}.tsp`、`src/server/routes/viewer.php`、`src/server/app/Http/Controllers/Api/Viewer/V1/Media/ListMediaController.php`、`src/server/packages/Media/Infrastructures/Viewer/MediaQueryService.php` まで揃っており、`src/viewer/src/features/media/api.ts` に `mediaRepository.all()` が実装済みである。一方で、メディア詳細ページ `src/viewer/src/pages/media/[mediaId].astro` は `getStaticPaths` のみ `mediaRepository.all()` に切り替えた暫定状態で、ページ本体は表示処理がコメントアウトされたまま `mediaId` を受け取るだけになっている。フラグメント側の `src/viewer/src/pages/fragments/media/[mediaId].astro` と表示コンポーネント `src/viewer/src/components/viewer/details/MediaDetailContent.astro` も依然として `src/viewer/src/data/mock/site-data` の `SITE_DATA.media` と `Q.songsOfMedia` に依存している

直近で完了した楽曲詳細 API(`docs/exec-plans/completed/20260511-viewer-song-detail-api.md`)が `GET /songs/{songId}` を `SongDetail` / `SongDetailResponse` / `SongDetailMediaSummary` の対称 shape で提供しており、メディア一覧 API の Non-Scope に「Viewer 向けメディア詳細 API(`GET /media/{mediaId}`)は本タスク完了後に別 exec-plan で扱う」と明記されている。本タスクではその欠落を埋める。管理画面向けに `src/contracts/src/admin/media/` や `src/server/app/Http/Controllers/Api/Admin/V1/Media/` があるが、内部管理用 shape のため Viewer にそのまま流用しにくい

## Goal

Viewer から `mediaId` 単位で公開メディアの詳細を取得できる API 境界を定義し、サーバー実装と `src/viewer` の詳細ページ実装を mock から API ベースへ切り替えられる状態にする

## Scope

- `src/contracts/src/viewer/media/service.tsp` に `GET /media/{mediaId}` の contract を追加し、`domain.tsp` と `transport.tsp` に `MediaDetail` / `MediaDetailResponse` / `MediaDetailSongSummary` 相当の model を追加する
- `src/server/packages/Media/Route/ViewerMediaRouteMap.php` に `Get` case を追加し、`src/server/routes/viewer.php` の media グループに `GET /media/{mediaId}` を登録する
- `src/server/packages/Media/Application/Viewer/Query/` に詳細 DTO(`MediaDetail`、`MediaDetailSongSummary`)と `MediaQueryServiceInterface::get()` を追加し、`src/server/packages/Media/Application/Viewer/UseCase/Get/` に `GetInputData` / `GetOutputData` / `GetUseCase` を新設する
- `src/server/packages/Media/Infrastructures/Viewer/MediaQueryService.php` に `get(string $mediaId): ?MediaDetail` を追加し、`media.is_display = true` のメディアと、公開中の関連楽曲を `songMediaLinks` 経由で eager load して返す
- `src/server/app/Http/Controllers/Api/Viewer/V1/Media/GetMediaController.php` と `src/server/app/Http/Presenters/Api/Viewer/V1/Media/GetPresenter.php` を追加し、`NotFoundError` を 404 に変換する
- `src/server/tests/Feature/Api/Viewer/V1/Media/` に `GetMediaTest.php` を追加し、公開メディアの取得、非公開楽曲の除外、非公開メディアおよび未知 `mediaId` の 404 を `assertExactJson` で検証する
- `src/viewer/src/features/media/api.ts` に `mediaRepository.get(mediaId)` を追加し、`src/viewer/src/features/media/types.ts` に `MediaDetail` 型を export する
- `src/viewer/src/pages/media/[mediaId].astro` と `src/viewer/src/pages/fragments/media/[mediaId].astro` を `mediaRepository.get(mediaId)` 経由の詳細取得に切り替え、`MediaDetailContent.astro` を新 shape に合わせて mock 依存(`Q.songsOfMedia` / `SITE_DATA`)を剥がす

## Non-Scope

- 管理画面向け `src/contracts/src/admin/media/` や `GET /admin/media/{mediaId}` の互換変更
- Viewer のメディア一覧 API のページング仕様変更
- メディア詳細ページの未提供セクション(リリース、イベント、再生数・いいね数などプラットフォーム別メトリクス)の本実装。既存のコメントアウト UI と暫定の見た目(プラットフォームバッジ、色タイル等)は雛形として残す
- メディア種別ごとの装飾(色・サムネイル)の最終デザイン決定
- 楽曲詳細 API の `SongDetailMediaSummary` shape 変更

## Acceptance Criteria

- Viewer contract と生成された OpenAPI に `GET /media/{mediaId}` が追加され、詳細レスポンスが少なくとも `mediaId`、`title`、`url`、`publishedAt`、`type`、`format`、`counts.songCount`、公開中の関連楽曲一覧(`songId`、`title`、`type` を持つ summary)を表現できる
- サーバー実装が `media.is_display = true` のメディアのみを返し、非公開メディアまたは存在しない `mediaId` に対して 404 を返す
- 詳細レスポンスの関連楽曲一覧に非公開楽曲(`songs.is_display = false`)が含まれず、`counts.songCount` は返却する公開楽曲 summary 件数と一致する
- `src/server/tests/Feature/Api/Viewer/V1/Media/GetMediaTest.php` で、公開メディアの 200 と期待 JSON、非公開楽曲がレスポンスから除外されること、非公開メディアと未知 `mediaId` が 404 になることが `assertExactJson` で検証されている
- `src/viewer/src/pages/media/[mediaId].astro` と `src/viewer/src/pages/fragments/media/[mediaId].astro` が `mediaRepository.get(mediaId)` を呼び、`MediaDetailContent.astro` を mock import なしで描画でき、TODO コメントが解消される
- `MediaDetailContent.astro` から `data/mock/site-data` への依存が削除され、関連楽曲セクションが API の `songs` summary を元に描画される(未提供セクションのコメントアウトは保持)

## Steps

1. ✅ `src/contracts/src/viewer/media/domain.tsp` に `MediaRelationCounts { songCount: int32; }` と `MediaDetailSongSummary`(`songId`、`title: title`、`type: SongType` のみ)と `MediaDetail`(`mediaId`、`title`、`url`、`publishedAt`、`type: MediaType`、`format: MediaFormat`、`counts: MediaRelationCounts`、`songs: MediaDetailSongSummary[]`)を追加する。`songId` / `title` / `SongType` / `SongTypeValue` は `viewer/songs/domain.tsp` 側で同名 namespace に既に定義されているためそのまま再利用する。`@example` を付ける
2. ✅ `src/contracts/src/viewer/media/transport.tsp` に `MediaDetailResponse { media: MediaDetail; }` を追加し、`@example` を付ける。`src/contracts/src/viewer/media/service.tsp` の `MediaService` に `getMedia(@path mediaId: mediaId): Ok<MediaDetailResponse> | NotFound | InternalServerError;` を追加する。命名は楽曲側 `getSong` と完全対称
3. ✅ `mise run contract:format:check` と `mise run contract:compile:viewer` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `GET /media/{mediaId}` と `MediaDetail` / `MediaDetailResponse` / `MediaDetailSongSummary` / `MediaRelationCounts` が出力されることを確認する
4. ✅ `src/server/packages/Media/Route/ViewerMediaRouteMap.php` に `case Get = 'viewer.media.get';` を追加する(楽曲側 `ViewerSongRouteMap::Get` と対称)
5. ✅ `src/server/packages/Media/Application/Viewer/Query/` に `MediaDetailSongSummary`(`string $songId, string $title, SongType $type`)と `MediaDetail`(`string $mediaId, string $title, string $url, DateTimeImmutable $publishedAt, MediaType $type, MediaFormat $format, array<MediaDetailSongSummary> $songs`)を追加し、`MediaQueryServiceInterface` に `public function get(string $mediaId): ?MediaDetail;` を追記する。`Song\Domain\Models\SongType` を import する
6. ✅ `src/server/packages/Media/Application/Viewer/UseCase/Get/` に `GetInputData`(`string $mediaId`)、`GetOutputData`(`MediaDetail $media`)、`GetUseCase`(`MediaQueryServiceInterface` を注入、`null` で `Err(new NotFoundError('メディア', $inputData->mediaId))`、それ以外は `Ok`)を、`Song\Application\Viewer\UseCase\Get` パターンそのままで新設する
7. ✅ `src/server/packages/Media/Infrastructures/Viewer/MediaQueryService.php` に `get(string $mediaId): ?MediaDetail` を実装する。`Media::query()->select(['media_id','title','url','published_at','type','format'])->where('media_id', $this->converter->toBin($mediaId))->where('is_display', true)->with(['songMediaLinks' => fn ($q) => $q->select(['media_id','song_id','order_no'])->whereHas('song', fn ($s) => $s->where('is_display', true))->orderBy('order_no'), 'songMediaLinks.song' => fn ($q) => $q->select(['song_id','title','type'])])->first()` で取得し、`songMediaLinks` を `MediaDetailSongSummary` に詰め替える。`null` の場合は `null` を返す
8. ✅ `src/server/app/Providers/Domain/MediaServiceProvider.php` の bind は `MediaQueryService` を既に Viewer 用にバインド済みなので追加変更は不要(インターフェース実装が広がるだけ)。バインド状況だけ確認する
9. ✅ `src/server/app/Http/Controllers/Api/Viewer/V1/Media/GetMediaController.php` を追加し、`Song\GetSongController` と同型で `handle(string $mediaId): JsonResponse` を実装する。`src/server/app/Http/Presenters/Api/Viewer/V1/Media/GetPresenter.php` を追加し、`ResolvesUseCaseError` を `use` した上で `media` キー配下に `mediaId, title, url, publishedAt(Y-m-d), type:{name,value}, format:{name,value}, counts:{songCount: count($songs)}, songs:[{songId,title,type:{name,value}}]` を出力する。`NotFoundError` は trait で 404 に解決される
10. ✅ `src/server/routes/viewer.php` の media グループに `Route::get('/{mediaId}', [GetMediaController::class, 'handle'])->name(ViewerMediaRouteMap::Get);` を追加する(楽曲側と並列)
11. ✅ `src/server/tests/Feature/Api/Viewer/V1/Media/GetMediaTest.php` を新設する。`GetSongTest.php` を雛形に、(a) 公開メディアと公開楽曲 2 件 + 非公開楽曲 1 件の関連を作って `assertExactJson` で `counts.songCount = 2`、`songs` 配列に非公開楽曲が含まれないこと、(b) `is_display = false` のメディアで 404、(c) 未知 `mediaId` で 404、を検証する。`storeMedia` / `storeSongs` ヘルパは既存の `EntityFactory` / `EntityStore` を使う
12. ✅ `src/viewer/src/features/media/types.ts` に `MediaDetail = generated.MediaDetail`、`MediaSong = generated.MediaDetailSongSummary` を追加 export する。`src/viewer/src/features/media/api.ts` に `get(mediaId: string): Promise<MediaDetail>` を追加し、`mediaServiceGetMedia` を呼んで `data.media` を返す。`mediaRepository` に `get` を生やす
13. ✅ `src/viewer/src/pages/media/[mediaId].astro` を `songs/[songId].astro` パターンに置き換える。`getStaticPaths` は `mediaRepository.all()` のまま、本体で `await mediaRepository.get(mediaId)` し、`Layout pageTitle={media.title}` の中で `MediaDetailContent` に `media={media}` で渡す。`SITE_DATA` / `MediaEntry` import は削除する
14. ✅ `src/viewer/src/pages/fragments/media/[mediaId].astro` を `mediaRepository.all()` + `mediaRepository.get(mediaId)` 経由に置き換える。`SITE_DATA` import を削除し、`MediaDetailContent` に新 shape の `media` を渡す
15. ✅ `src/viewer/src/components/viewer/details/MediaDetailContent.astro` を新 shape (`Props { media: MediaDetail }`) に書き換える。`Q.songsOfMedia` と `data/mock/site-data` 依存を撤廃し、関連楽曲は `media.songs`(`songId` / `title` / `type.name`)からチップを描画して `/songs/{songId}` にリンクする。`postEntry` 分岐の入力は `media.type.value`(`socialPost` / `article` 判定)から導出する。プラットフォーム別バッジ、`color`、`text`、`views`、`likes`、`reposts`、`readTime`、`hasImage`、`description` 等 API に存在しないフィールドを使っている枝はコメントアウトとして残し、`var(--accent)` などの暫定値を一時値として置く(viewer メモリのルールに従い未提供セクションの雛形は削除しない)。`MVThumb` の `color` / `label` には API 由来の代替値(例: 既定色、`media.type.name + ' · ' + media.mediaId`)を渡す

## Decision Log

- 2026-05-11: `GET /media/{mediaId}` は関連楽曲を full detail で含めず `MediaDetailSongSummary { songId, title, type }` のみ返す。viewer のメディア詳細画面で関連楽曲チップに必要なのはリンク先・タイトル・種別のみで、`description` や `thumbnail` を加えると楽曲詳細 API の責務と被るため。`publishedAt` も楽曲ドメインでは順序キー以上の意味を持たないため summary には含めない
- 2026-05-11: `counts.songCount` は返却する公開楽曲 summary 件数と一致させる。楽曲側 `counts.mediaCount` と同じく一覧と詳細で件数が食い違うと viewer の UX と feature test の検証が不安定になるため、Infrastructures 層で `is_display = true` を `with` の `whereHas` で適用し、配列件数を Presenter 側で `count($songs)` から導出する
- 2026-05-11: 404 への変換経路は楽曲詳細 API と完全同型にする。`MediaQueryService::get` は非公開・存在しないどちらも `null` を返し、`GetUseCase` が `Err(new NotFoundError('メディア', $mediaId))` を返し、`GetPresenter` の `ResolvesUseCaseError` trait が 404 に解決する。Domain の `MediaRepositoryInterface` には触らず Admin 側へ影響を出さない
- 2026-05-11: `is_display = true` のフィルタは Infrastructures 層 (`MediaQueryService`) で適用する。楽曲側 (`SongQueryService::get`) と対称にし、UseCase / Domain を「公開のみ」前提に保つ
- 2026-05-11: `MediaDetailContent.astro` の mock 依存剥がしは「ユーザーの恒久ルール(viewer メモリ): `src/viewer/` のコメントアウト UI は未提供セクションの雛形として残す」を最優先する。プラットフォームバッジ・色タイル・views/likes/reposts/readTime/hasImage/description などプラットフォーム別メトリクスは API に存在しないため、既存表示ブロックはコメントアウトして雛形として残し、削除しない。Non-Scope の「メディア詳細ページの未提供セクションの本実装」とも整合する
- 2026-05-11: 関連楽曲チップの色 (`color-block` の `--tile-color`) は API に色情報が無く、Non-Scope の「メディア種別ごとの装飾の最終デザイン決定」に該当するため、`var(--accent)` を仮置きする TODO コメント付きで暫定対応する。楽曲詳細側のジャケット色暫定対応と同じ方針
- 2026-05-11: contract の `MediaDetail` には `description` を含めない。`media` テーブル / `Media` ドメインモデルに楽曲のような `description` カラムが無く、本タスクで新規にスキーマを増やすのは Non-Scope(メディア詳細ページの未提供セクションの本実装)に該当するため

## Validation

- `mise run contract:format:check`
- `mise run contract:compile:viewer` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `GET /media/{mediaId}`、`MediaDetailResponse`、`MediaDetail`(`mediaId` / `title` / `url` / `publishedAt` / `type` / `format` / `counts.songCount` / `songs`)、`MediaDetailSongSummary`(`songId` / `title` / `type`)が出力されることを確認する
- `mise run api:ecs`, `mise run api:phpstan`, `mise run api:arkitect` をパスする
- `mise run api:test --filter Viewer/V1/Media` で `ListMediaTest` と新規 `GetMediaTest` がグリーン。`GetMediaTest` は公開メディアの 200 と固定 JSON、非公開楽曲がレスポンス `songs` 配列から除外されること、`counts.songCount` が返却件数と一致すること、非公開メディアと未知 `mediaId` の 404 を `assertExactJson` で検証する
- `mise run api:test --filter Admin/V1/Media` で Admin 側に差分が無いことを確認する
- `cd src && bun --filter viewer build` で SSG ビルドが通り、`src/viewer/src/pages/media/[mediaId].astro` と `src/viewer/src/pages/fragments/media/[mediaId].astro` が `mediaRepository.get(mediaId)` 経由で全公開メディア分のページを生成すること、`MediaDetailContent.astro` の mock import (`data/mock/site-data`, `Q.songsOfMedia`) が消えていることを確認する
- viewer 側で関連楽曲チップが API の `songs` summary を元に描画され、`/songs/{songId}` リンクが正しく張られていること、未提供セクションのコメントアウトが保持されていることを目視確認する
