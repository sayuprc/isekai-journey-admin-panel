# Viewer トップページ集計 API

## Background

Viewer のトップページ `src/viewer/src/pages/index.astro` は、現在 `src/viewer/src/data/mock/site-data.ts` の `SITE_DATA.songs.length` を直接参照して楽曲数を表示している。Viewer 側では `src/contracts/src/viewer/main.tsp` から songs / media の TypeSpec contract を生成し、`src/server/routes/viewer.php` の `/v1/songs` `/v1/media` と `src/viewer/src/generated/` の client を使う構造が既にあるため、トップページの件数も mock ではなく Viewer API 経由で取得できる境界を追加する必要がある

将来的にはトップページでメディア数やリリース数も同じ用途で表示する想定があるため、楽曲数だけを表す API 名や response 名にすると拡張時に不自然になる

## Goal

Viewer トップページに表示する件数集計を取得する API を追加し、初回は公開対象の楽曲数を返せるようにする。API contract / server / viewer の各層で、将来 `mediaCount` や `releaseCount` を同じ集計レスポンスへ追加できる命名と shape にする

## Scope

- `src/contracts/src/viewer/main.tsp` と新規または既存の Viewer TypeSpec モジュールで、トップページ集計用の endpoint / response schema を定義する
- `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml`、`src/server/Generated/`、`src/viewer/src/generated/` へ contract 変更を反映する
- `src/server/routes/viewer.php` に Viewer v1 のトップページ集計 route を追加する
- `src/server/app/Http/Controllers/Api/Viewer/V1/` と `src/server/app/Http/Presenters/Api/Viewer/V1/` にトップページ集計用 controller / presenter を追加する
- `src/server/packages/Song/Application/Viewer` と `src/server/packages/Song/Infrastructures/Viewer`、または複数ドメイン集計に適した Viewer 用 query/use case 層で、公開対象 `songs.is_display = true` の楽曲数を取得する
- `src/server/app/Providers/Domain/*ServiceProvider.php` で必要な query service binding を追加する
- `src/viewer/src/pages/index.astro` と必要な `src/viewer/src/features/` または `src/viewer/src/shared/` の API repository で、mock の楽曲数参照を API 経由の値へ置き換える

## Non-Scope

- メディア数、リリース数を実際に返す実装
- トップページの見た目やレイアウトの変更
- 楽曲一覧 API `/v1/songs`、メディア一覧 API `/v1/media` の response shape 変更
- Admin API、Admin 画面、認証付き管理機能の変更
- release の Viewer 一覧・詳細 API の新設

## Acceptance Criteria

- Viewer contract に、楽曲専用ではなくトップページ集計を表す endpoint と response schema が追加されている
- response schema は初回フィールドとして公開対象楽曲数を `songCount` として返し、将来 `mediaCount` / `releaseCount` を同じ集計モデルへ追加しても命名が破綻しない
- server の Viewer API は、`is_display = true` の楽曲だけを数えて `songCount` に返す
- `src/viewer/src/pages/index.astro` のトップページ楽曲数表示が `SITE_DATA.songs.length` ではなく生成 client 経由の API 結果を使う
- 生成 OpenAPI と viewer generated client にトップページ集計 API が反映され、既存の `/v1/songs` `/v1/media` の contract 互換性を壊していない
