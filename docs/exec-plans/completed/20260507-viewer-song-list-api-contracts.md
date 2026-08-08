# Execution Plan

## Title

Viewer 楽曲一覧 API contracts 定義

## Status

completed

## Background

Viewer の楽曲一覧画面は現在 `src/viewer/src/data/site-data.js` と `src/viewer/src/data/site-helpers.ts` のダミーデータを `src/viewer/src/pages/songs/index.astro` から直接参照して構築している。`src/viewer/src/pages/songs/[songId].astro` や `src/viewer/src/pages/fragments/songs/[songId].astro` も同じ静的データを `getStaticPaths()` に使っており、Viewer 全体は SSG 前提のデータ取得に寄っている。一方で `src/contracts/src/viewer/main.tsp` は空で、Viewer 向け API contract が未定義であり、既存の `src/contracts/src/admin/songs/service.tsp` の検索 API は認証付き・ページング付きの管理画面向け shape なので、Viewer のビルド時取得にはそのまま適用しにくい。

## Goal

Viewer の楽曲一覧画面を API ベースへ移行するための最初の作業として、`src/contracts` に Viewer 向けの楽曲一覧 contract を定義する。Astro SSG のビルド時に一括取得しやすく、一覧表示に必要な公開情報と最小限の関連集計を表現できる API 境界を明確にする。

## Scope

- `src/contracts/src/viewer/main.tsp` から参照される Viewer 楽曲一覧 contract の追加
- `src/contracts/src/viewer/songs/` 配下の TypeSpec モジュール新設、または同等の Viewer songs contract 定義
- `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に反映される Viewer songs 一覧 endpoint / schema の定義
- 一覧画面の要件確認対象として `src/viewer/src/pages/songs/index.astro`、`src/viewer/src/data/site-data.js`、`src/viewer/src/data/site-helpers.ts` で現在利用している項目と並び順ロジックの整理
- downstream 実装の前提確認対象として `src/server/packages/Song` と `src/server/routes` にある既存 song モジュールの再利用可能性の把握

## Non-Scope

- `src/server` の controller / use case / presenter 実装追加
- `src/viewer` の API クライアント生成物利用への置き換え
- 楽曲詳細 API、イベント・リリース・メディア一覧 API の contract 定義
- 既存の Admin 向け `songs/search` contract の互換変更

## Acceptance Criteria

- `src/contracts` に Viewer 向け楽曲一覧 API の contract が追加され、生成される OpenAPI に少なくとも 1 つの songs 一覧 endpoint と response schema が現れる
- response schema が Viewer 一覧 API の現時点の責務として必要な一覧情報を表現できる
- response schema が楽曲単位の runtime 個別取得を前提とせず、Viewer ビルド時に一覧ページ用データを一括取得できる shape になっている
- contract 上で表示対象制御の扱いが明示され、Admin 向け `isDisplay` を Viewer 一覧でどう反映するか判断できる

## Steps

1. ✅ `src/viewer/src/pages/songs/index.astro` と `src/viewer/src/data/site-helpers.ts` を基準に、一覧画面が実際に使う項目を棚卸ししつつ、初回 Viewer contract では `title` / `type` / `description` / 公開に必要な最小件数集計へ絞り込む。
2. ✅ `src/contracts/src/viewer/songs/` 配下に `domain.tsp` と `service.tsp` と `transport.tsp` を新設し、Viewer 用の `SongListItem`、最小件数集計モデル、列挙型、表示可否・返却順の意味を表す型を定義する。
3. ✅ `src/contracts/src/viewer/songs/service.tsp` に Viewer 専用の `GET /songs` endpoint を追加し、SSG の build 時一括取得を既定とした response を定義する。Admin の `order_no` / offset pagination をそのまま持ち込まない。
4. ✅ 同 endpoint の query と response 拡張点として、将来分割取得が必要になった場合に備えた seek pagination 方針を contract コメントと transport 型へ織り込む。初回実装では cursor を必須化せず、`nextCursor` を持てる envelope か、追加時に後方互換で拡張しやすい response 形状に整える。
5. ✅ `src/contracts/src/viewer/main.tsp` から Viewer songs モジュールを import し、namespace 配下へ組み込んで OpenAPI 生成対象に含める。
6. ✅ `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` を `src/contracts/package.json` の `compile:viewer` で再生成し、Viewer songs endpoint・schema・返却順説明が期待どおり出ることを確認する。
7. ✅ generated OAS を見ながら、初回 Viewer 一覧 API の責務として必要な `title`、`type`、`description`、`mediaCount` が 1 レスポンスで満たせることを検証する。
8. ✅ downstream 前提の確認として `src/server/packages/Song/Application/Admin/UseCase/Search`、`src/server/packages/Song/Infrastructures/Admin/SongQueryService.php`、`src/server/routes/admin.php` を参照し、Viewer 実装時には Admin 検索の offset pagination を流用せず、Viewer 専用 route / query / presenter を別建てする前提を Decision Log に残す。
9. ✅ 実装後に exec plan の Scope / Acceptance Criteria と差分がないかを見直し、Viewer 一覧 API contract が「一括取得を基本、件数増加時のみ seek pagination に移行可能」という判断を文書化して次フェーズへ引き渡す。

## Decision Log

- 2026-05-07: Viewer の楽曲一覧は Astro SSG の build 時取得を前提にするため、初回 contract は `GET /songs` の全件一括取得を基本形とする。現在の `songs/index.astro` は一覧表示に必要な集計値と初回リリース日をその場で計算しているが、API 化後は build 時点で 1 回の取得結果へ集約しておく方が、ページごとの追加 fetch や N+1 的な補助 API を避けられる。
- 2026-05-07: 件数増加で一括取得が重くなった場合の分割取得は offset ではなく seek pagination を採用する。Viewer は一覧を順に取り切るバッチ的ユースケースなので、安定キーを cursor にした方が、追加データや並び替えの影響で取りこぼし・重複が起きにくい。
- 2026-05-07: Admin の `songs/search` は認証前提かつ `page` / `per_page` の offset pagination と管理用検索条件を持つため、Viewer へそのまま流用しない。Viewer contract は公開 API・SSG 最適化・表示用集計済みレスポンスを優先し、server 実装も別 route / use case / presenter で受ける前提にする。
- 2026-05-07: 表示対象制御は Admin の `isDisplay` を単純露出するのではなく、Viewer contract 側で「公開対象のみ返す」方針を基本とする。必要なら将来 filter を増やせるようにしつつ、初回 contract では公開画面に不要な内部状態を持ち込まない。
- 2026-05-07: 現在の server 側 Song ドメインでは `titleEn` と `duration` と `firstReleaseDate` 集計が未定義のため、Viewer contract では UI に必要な公開 read model を先行定義する。server 実装フェーズでは Song 本体と関連メディア集計を束ねる Viewer 専用 query / presenter で補完する前提にする。
- 2026-05-07: Plan では `src/server/packages/Song/Application/Admin/UseCase/Search` ディレクトリを参照対象にしていたが、実際の入口クラスは `Search/SearchUseCase.php` だった。確認した実装は `SongQueryService::search()` で `limit + offset` と `maxPage()` を持つため、Viewer へ流用しない前提は維持する。
- 2026-05-07: レビュー指摘とユーザー判断に合わせ、初回 contract には `color` を追加しない。`titleEn` / `duration` / event / performance 件数も contract から外し、Viewer 一覧 API は公開 read model の最小責務に絞る。
- 2026-05-07: 後続判断により `firstReleaseDate` 自体を contract から削除する。初回 Viewer 一覧 API では並び順の契約化を見送り、最小公開 read model に絞る。
- 2026-05-07: さらに後続判断により release 機能も未提供のため、`releaseCount` を contract から削除し、件数集計は `mediaCount` のみに絞る。

## Validation

- `cd src/contracts && bun run format:check`
- `cd src/contracts && bun run compile:viewer`
- 生成された `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `GET /songs` と Viewer songs schema が出力されていることを確認する。
- generated OAS の response schema を見て、初回 Viewer 一覧 API の責務として必要な `title`、`description`、カテゴリ表示用値、`mediaCount` を 1 レスポンスから取得できることを確認する。
- response 定義または説明文に一括取得が基本であることと、将来の seek cursor 拡張余地が残っていることを確認する。
- Admin 側 contract / query 実装との差分を確認し、Viewer contract に `page` / `per_page` の offset pagination が入っていないことを確認する。

### Validation Results

- 2026-05-07: `cd src/contracts && bun run format:check` は初回に整形差分で失敗したため、`bun run format` 実行後に再整形済み。
- 2026-05-07: `cd src/contracts && bun run compile:viewer` は成功し、`src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `GET /songs`、`SongListResponse`、`SongListItem`、`SongRelationCounts`、`SongListOrder`、`nextCursor` が出力された。
- 2026-05-07: generated OAS 上で `title`、`titleEn`、`description`、`duration`、`firstReleaseDate`、`type`、`counts.releaseCount` / `performanceCount` / `mediaCount` / `eventCount` が 1 レスポンスに揃うことを確認した。
- 2026-05-07: generated OAS 上で返却順 `first_release_date_desc`、一括取得前提の description、将来の seek cursor 用 `cursor` / `limit` / `nextCursor` が確認でき、`page` / `per_page` は含まれていないことを確認した。
- 2026-05-07: レビュー反映後の generated OAS 上で `title`、`description`、`type`、`counts.mediaCount` が 1 レスポンスに揃い、`titleEn` / `duration` / `releaseCount` / `performanceCount` / `eventCount` / `firstReleaseDate` は除外されていることを確認した。
- 2026-05-07: `firstReleaseDate` 削除後も一括取得前提の description と将来の seek cursor 用 `cursor` / `limit` / `nextCursor` が確認でき、`page` / `per_page` は含まれていないことを確認した。
