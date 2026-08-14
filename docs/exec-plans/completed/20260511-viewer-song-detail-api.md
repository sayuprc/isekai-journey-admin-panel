# Execution Plan

## Title

Viewer 向け楽曲詳細取得 API

## Status

completed

## Background

Viewer の楽曲一覧 API は `src/contracts/src/viewer/songs/*`、`src/server/routes/viewer.php`、`src/server/app/Http/Controllers/Api/Viewer/V1/Song/ListSongController.php` まで実装済みだが、詳細ページの `src/viewer/src/pages/songs/[songId].astro` と `src/viewer/src/pages/fragments/songs/[songId].astro` は `songId` を受け取るだけで取得処理が未実装のまま止まっている。表示コンポーネント `src/viewer/src/components/viewer/details/SongDetailContent.astro` は現在 `src/viewer/src/data/mock/site-data.ts` の関係データに依存しており、Viewer 向けの公開詳細 API がない。管理画面向けには `src/contracts/src/admin/songs/service.tsp` の `getSong` と `src/server/app/Http/Controllers/Api/Admin/V1/Song/GetSongController.php` があるが、認証前提かつ内部管理用 shape のため Viewer へそのまま流用しにくい

## Goal

Viewer から `songId` 単位で公開楽曲の詳細を取得できる API 境界を定義し、サーバー実装と `src/viewer` の詳細ページ実装に着手できる状態にする

## Scope

- `src/contracts/src/viewer/songs/service.tsp` に Viewer 向け `GET /songs/{songId}` の contract を追加し、必要なら `domain.tsp` と `transport.tsp` に詳細用 model を追加する
- `src/server/routes/viewer.php` と `src/server/packages/Song/Route/ViewerSongRouteMap.php` に詳細取得 route を追加する
- `src/server/app/Http/Controllers/Api/Viewer/V1/Song/` と `src/server/app/Http/Presenters/Api/Viewer/V1/Song/` に詳細取得の controller / presenter を追加する
- `src/server/packages/Song/Application/Viewer/` と `src/server/packages/Song/Infrastructures/Viewer/SongQueryService.php` に、公開楽曲と公開中の関連メディアを `songId` 単位で返す query / use case を追加する
- `src/server/tests/Feature/Api/Viewer/V1/Song/` に、公開楽曲取得・非公開除外・存在しない ID の扱いを確認するテストを追加する
- `src/viewer/src/features/songs/api.ts`、`src/viewer/src/pages/songs/[songId].astro`、`src/viewer/src/pages/fragments/songs/[songId].astro`、必要に応じて `src/viewer/src/components/viewer/details/SongDetailContent.astro` を、mock ではなく generated client 経由の詳細取得へ切り替える

## Non-Scope

- 管理画面向け `src/contracts/src/admin/songs/*` や `GET /admin/songs/{songId}` の互換変更
- Viewer の楽曲一覧 API のページング仕様変更
- リリース一覧・イベント一覧・歌唱履歴など、現在 `SongDetailContent.astro` でコメントアウトされている未提供セクションの本実装
- 楽曲ジャケット色やダミーデータ由来の装飾情報の最終デザイン決定

## Acceptance Criteria

- Viewer contract と generated OpenAPI に `GET /songs/{songId}` が追加され、詳細レスポンスが少なくとも `songId`、`title`、`description`、`type`、`lyricists`、`composers`、`arrangers`、`counts.mediaCount`、公開中の関連メディア一覧を表現できる
- サーバー実装が公開中の楽曲のみを返し、非公開楽曲または存在しない `songId` では 404 を返す
- 詳細レスポンスの関連メディア一覧に非公開メディアが含まれない
- `src/viewer/src/pages/songs/[songId].astro` と `src/viewer/src/pages/fragments/songs/[songId].astro` が `songRepository` 経由で詳細データを取得する前提に更新され、`songId` 取得 TODO が解消される

## Steps

1. ✅ `src/contracts/src/viewer/songs/service.tsp` に `GET /songs/{songId}` を追加し、`src/contracts/src/viewer/songs/domain.tsp` と `src/contracts/src/viewer/songs/transport.tsp` に `SongDetail` / `SongDetailResponse` / `SongDetailMediaSummary` 相当の model を定義する。media は full detail を含めず、`mediaId`、`title`、`type`、`publishedAt` を返す最小 shape に留める
2. ✅ contract 追加に合わせて generated client の利用箇所を確認し、`src/viewer/src/features/songs/types.ts` がある場合は詳細 shape を受けられるよう調整対象に含める。`songRepository` で一覧と詳細の両方を扱える前提を固める
3. ✅ `src/server/packages/Song/Route/ViewerSongRouteMap.php` に詳細 route 名を追加し、`src/server/routes/viewer.php` に `GET /songs/{songId}` を登録する。既存 `ListSongController` と並ぶ形で `GetSongController` を追加できるルーティング構成にする
4. ✅ `src/server/packages/Song/Application/Viewer/Query/` に詳細取得用 DTO と query interface の戻り値を追加し、`src/server/packages/Song/Application/Viewer/UseCase/Get/` に入力 DTO・出力 DTO・use case を新設する。存在しない ID / 非公開楽曲を 404 に変換できるエラー経路をここで定義する
5. ✅ `src/server/packages/Song/Infrastructures/Viewer/SongQueryService.php` に詳細取得メソッドを追加し、`songs.is_display = true` の楽曲のみ取得する。あわせて `persons` と `songMediaLinks.media` を eager load し、関連 media は `media.is_display = true` のものだけを `order_no` 順で summary 化する
6. ✅ `src/server/app/Http/Controllers/Api/Viewer/V1/Song/GetSongController.php` と `src/server/app/Http/Presenters/Api/Viewer/V1/Song/GetPresenter.php` を追加し、詳細 DTO を viewer contract のレスポンス shape に整形する。`counts.mediaCount` は返却する公開 media summary 件数と一致させる
7. ✅ `src/server/tests/Feature/Api/Viewer/V1/Song/` に詳細取得 API の feature test を追加し、公開楽曲の詳細取得、非公開 media の除外、非公開楽曲の 404、未知 `songId` の 404 を固定 JSON で検証する
8. ✅ `src/viewer/src/features/songs/api.ts` に `songRepository.get(songId)` を追加し、generated client の詳細 API を呼ぶようにする。`src/viewer/src/pages/songs/[songId].astro` と `src/viewer/src/pages/fragments/songs/[songId].astro` は `getStaticPaths` を一覧のまま使いつつ、ページ本体では `songId` から詳細取得して TODO を解消する
9. ✅ `src/viewer/src/components/viewer/details/SongDetailContent.astro` を API の詳細 shape に合わせて置き換える。mock 依存の `Q.mediaOfSong()` / `Q.releasesOfSong()` を外し、作曲者情報・説明・`counts.mediaCount`・関連 media 一覧を `song` props から描画する。未提供の release セクションは引き続き非表示のままにする

## Decision Log

- 2026-05-11: `GET /songs/{songId}` には関連 media の full detail を含めず、viewer の楽曲詳細画面で必要な summary のみ返す。画面初期表示を 1 リクエストで完結させつつ、media 詳細 API の責務肥大化を避けるため
- 2026-05-11: media summary の項目は `mediaId`、`title`、`type`、`publishedAt` を基本とする。現行 `SongDetailContent.astro` の関連メディア表示はリンク先、タイトル、種別、日付があれば成立し、thumbnail や説明文は別 API に寄せられるため
- 2026-05-11: 一覧 API の `counts.mediaCount` と同じ公開判定を詳細 API にも適用し、詳細レスポンスの `counts.mediaCount` は公開 media summary 件数と一致させる。一覧と詳細で表示件数が食い違うと viewer 側の UX と検証が不安定になるため
- 2026-05-11: 楽曲ジャケットの色は API に色情報が無く、Non-Scope の「ジャケット色の最終デザイン決定」に該当するため、`SongDetailContent.astro` では `var(--accent)` を仮置きする TODO コメント付きで暫定対応する

## Validation

- `GET /songs/{songId}` の contract 生成結果を確認し、viewer 用 OpenAPI / generated client から詳細 API が呼べることを確認する
- feature test で、公開楽曲は 200 と期待 JSON、非公開 media はレスポンス配列に含まれないこと、非公開楽曲と未知 ID は 404 になることを検証する
- viewer 側では `src/viewer/src/pages/songs/[songId].astro` と `src/viewer/src/pages/fragments/songs/[songId].astro` が `songRepository.get(songId)` を呼び、mock import なしで `SongDetailContent.astro` を描画できることを確認する
- 必要なら `songRepository.list()` の件数と詳細ページの `counts.mediaCount` / media 一覧表示が、公開・非公開混在データでも整合することを目視またはテストで確認する
