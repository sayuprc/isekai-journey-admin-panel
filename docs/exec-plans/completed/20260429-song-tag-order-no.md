# Title

楽曲タグに order_no を追加

## Status

completed

## Background

楽曲タグ定義では `order_no` が契約に存在せず、`SongTag` のレスポンスや更新入力、検索ソート条件が名前ベースに限定されている。ほかのマスタ系（`Creator`、`Performer`、`Song`）は `order_no` を持っており、管理対象の並び順を統一して扱えない。

## Goal

楽曲タグでも `order_no` を正式な契約とデータ定義に含め、一覧・検索・更新で表示順を扱える状態にする。

## Scope

- `src/contracts/src/admin/song-tags/domain.tsp` の `SongTag` に `orderNo` を追加する
- `src/contracts/src/admin/song-tags/transport.tsp` の更新リクエスト/レスポンス定義に `orderNo` の入出力要件を反映する
- `src/contracts/src/admin/song-tags/service.tsp` の検索ソート条件とデフォルト順を `order_no` 対応に揃える
- `src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml` と `src/admin/src/generated/types.gen.ts` など生成物に変更を反映する

## Non-Scope

- `song_taggings` の並び順仕様変更
- 楽曲タグ以外のマスタ（クリエイター、歌唱者、楽曲）の仕様変更
- 管理画面の見た目だけを先行して変える対応

## Acceptance Criteria

- `SongTag` の契約定義と生成された OpenAPI/TypeScript 型に `order_no` が含まれる
- 楽曲タグ検索のソート条件で `order_no` を指定でき、デフォルトソートも `order_no` になる
- 楽曲タグ更新入力で `order_no` を受け取れる定義になっている

## Steps

1. ✅ `src/contracts/src/admin/song-tags/domain.tsp` を更新し、`Creator` / `Performer` と同じパターンで `SongTag` に `orderNo: orderNo;` を追加する。`@example` にも `orderNo` を含め、レスポンス契約の Source of Truth を揃える。
2. ✅ `src/contracts/src/admin/song-tags/transport.tsp` を更新し、`SongTagUpdateRequest` に `orderNo: orderNo;` を追加する。`SongTagCreateRequest` は今回の Acceptance Criteria と Non-Scope に含まれないため変更しない。
3. ✅ `src/contracts/src/admin/song-tags/service.tsp` を更新し、`SongTagSearchSortBy` に `orderNo: "order_no"` を追加する。`searchSongTags` の `sort` デフォルト値も `SongTagSearchSortBy.orderNo` に変更し、他マスタの検索契約と整合させる。
4. ✅ `mise run contract:compile:admin` を実行して `src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml` を再生成し、`SongTag` スキーマ、`SongTagUpdateRequest`、`SongTagSearchSortBy`、`/song-tags/search` のデフォルトクエリに `order_no` が反映されることを確認する。
5. ✅ `mise run generate:client:admin` を実行して `src/admin/src/generated/types.gen.ts` と `src/admin/src/generated/index.ts` を更新し、`SongTag` 型、`SongTagUpdateRequest` 型、`SongTagSearchSortBy` 型に `order_no` 対応が反映されることを確認する。
6. ✅ `mise run generate:server` を実行して `src/server/Generated/lib/Model/SongTag.php`、`src/server/Generated/lib/Model/SongTagUpdateRequest.php`、`src/server/Generated/lib/Model/SongTagSearchSortBy.php`、必要に応じて `src/server/Generated/lib/Api/SongTagApi.php` を更新し、サーバー側生成物も契約に追従させる。
7. ✅ 生成差分を確認し、`song-tags` 以外の手編集や `dump.sql` / スキーマ変更が含まれていないことを確認する。不要な差分が混ざる場合は再生成手順を見直し、契約定義と生成物だけに差分を閉じ込める。

## Decision Log

- 2026-04-29: 今回の対象は「契約定義と生成物のみ」とし、`src/server/dump.sql` と Atlas スキーマは変更しない。
- 2026-04-29: `SongTag` の `orderNo` 追加パターンは `Creator` / `Performer` を踏襲し、同じ `orderNo` 共通 scalar を使う。
- 2026-04-29: 更新入力は Acceptance Criteria に合わせて `SongTagUpdateRequest` のみ拡張し、作成入力への `orderNo` 追加はこの計画には含めない。
- 2026-04-29: 検索ソートは既存マスタと同様に `order_no` を enum 値にし、デフォルトも `order_no` に寄せる。
- 2026-04-29: 生成物は OpenAPI、Admin クライアント、Server OpenAPI クライアントまで更新対象に含める。

## Validation

- `mise run contract:compile:admin` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml` に `SongTag.orderNo`、`SongTagUpdateRequest.orderNo`、`SongTagSearchSortBy.order_no`、検索 API の `sort` デフォルト `order_no` が出力されることを確認する。
- `mise run generate:client:admin` を実行し、`src/admin/src/generated/types.gen.ts` の `SongTag` / `SongTagUpdateRequest` / `SongTagSearchSortBy` が契約どおり更新されることを確認する。
- `mise run generate:server` を実行し、`src/server/Generated/lib/Model/SongTag*.php` と `src/server/Generated/lib/Api/SongTagApi.php` に `order_no` 関連定義が反映されることを確認する。
- `git diff -- docs/exec-plans/active/20260429-song-tag-order-no.md src/contracts/src/admin/song-tags src/contracts/generated/oas src/admin/src/generated src/server/Generated` 相当の差分確認で、変更が契約定義と生成物に限定されていることを確認する。
