# Viewer 一覧 API への詳細データ統合

## Background

Viewer は SSG でページを生成しているが、楽曲詳細ページとメディア詳細ページの `getStaticPaths` で一覧 API から取得した各 ID ごとに詳細 API を並列実行している。現在の詳細レスポンスは一覧レスポンスに関連データを足した程度の差分であり、ビルド時に `/v1/songs/{songId}` と `/v1/media/{mediaId}` を多数叩く構造がサーバー負荷になっている。

`src/contracts/src/viewer/songs/*` と `src/contracts/src/viewer/media/*` には一覧 API と詳細 API が別々に定義され、サーバー側も `List` / `Get` の controller、presenter、use case、query DTO が分かれている。Viewer 側では `src/viewer/src/features/songs/api.ts` と `src/viewer/src/features/media/api.ts` に `all()` と `get()` があり、詳細ページ・fragment ページが `all()` の結果から `get()` を呼んでいる。

## Goal

Viewer の楽曲一覧 API とメディア一覧 API に、現在の詳細ページ表示に必要な詳細データを含める。SSG は一覧取得だけで通常詳細ページと fragment 詳細ページを生成できるようにし、Viewer 向け詳細 API は contract、server route、generated client、viewer 呼び出しから削除する。

## Scope

- `src/contracts/src/viewer/songs/{domain,service,transport}.tsp` と `src/contracts/src/viewer/media/{domain,service,transport}.tsp` の一覧 response shape を詳細相当に拡張し、`getSong` / `getMedia` endpoint を削除する
- `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml`、`src/server/Generated/`、`src/viewer/src/generated/` に contract 変更を反映する
- `src/server/routes/viewer.php` から Viewer 向け `GET /v1/songs/{songId}` と `GET /v1/media/{mediaId}` を削除する
- `src/server/app/Http/Controllers/Api/Viewer/V1/{Song,Media}` と `src/server/app/Http/Presenters/Api/Viewer/V1/{Song,Media}` の一覧 presenter を新 shape に対応させ、不要になる詳細 controller / presenter を削除する
- `src/server/packages/Song/Application/Viewer`、`src/server/packages/Song/Infrastructures/Viewer/SongQueryService.php`、`src/server/packages/Media/Application/Viewer`、`src/server/packages/Media/Infrastructures/Viewer/MediaQueryService.php` の一覧 query / DTO / use case を、公開中の関連データを含めて返す形に更新し、不要な詳細取得用 use case / DTO を削除する
- `src/server/packages/{Song,Media}/Route/Viewer*RouteMap.php` から詳細 route 名を削除する
- `src/server/tests/Feature/Api/Viewer/V1/{Song,Media}` の一覧 API テストを新 shape に更新し、削除される詳細 API テストを整理する
- `src/viewer/src/features/{songs,media}/{api,types}.ts`、`src/viewer/src/pages/{songs,media}/[*.astro]`、`src/viewer/src/pages/fragments/{songs,media}/[*.astro]` を、一覧データだけで詳細ページを生成する形に更新する

## Non-Scope

- Admin API、Admin 画面、管理用 contract の変更
- Viewer のリリース機能の API 化、または `src/viewer/src/data/mock/site-data.ts` 全体の撤廃
- 楽曲一覧・メディア一覧のページング方式、並び順、公開判定の仕様変更
- 詳細ページのデザイン変更、未提供セクションの追加実装
- DB スキーマ変更や新しい永続化項目の追加

## Acceptance Criteria

- Viewer contract から `GET /songs/{songId}` と `GET /media/{mediaId}` が削除され、生成 OpenAPI と generated client からも詳細 API 呼び出しが消えている
- `GET /songs` の各 item が、現在の `SongDetail` 相当の `media` summary を含み、`counts.mediaCount` が返却される公開 media 件数と一致している
- `GET /media` の各 item が、現在の `MediaDetail` 相当の `counts.songCount` と `songs` summary を含み、非公開楽曲を含めない
- サーバーの Viewer route、controller、presenter、use case、query interface から詳細 API 専用の公開口が削除され、一覧 API テストで新 response shape と非公開関連データの除外が検証されている
- `src/viewer/src/features/songs/api.ts` と `src/viewer/src/features/media/api.ts` に詳細 API 呼び出しが残っておらず、通常詳細ページと fragment 詳細ページの `getStaticPaths` が `Promise.all(...repository.get(...))` なしで一覧データを props に渡している
- `src/viewer` のビルド時に Viewer 詳細 API へのリクエストが発生せず、楽曲・メディアの一覧ページ、通常詳細ページ、fragment 詳細ページが生成できる
