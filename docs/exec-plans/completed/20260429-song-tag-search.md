# Title

楽曲タグ検索

## Status

completed

## Background

`src/contracts/src/admin/song-tags` と生成済み OpenAPI / SDK には `song-tags/search` の契約が存在するが、実際に利用可能な検索機能はまだ揃っていない。`src/server/routes/admin.php` には `song-tags` ルートが未配線で、`src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php` も空実装のままである。管理画面側も `src/admin/src/server/index.ts` に `song-tags` BFF ルートがなく、`src/admin/src/pages` と `src/admin/src/components` に楽曲タグ一覧 UI が存在しないため、契約があってもユーザーは楽曲タグを検索できない。

## Goal

管理画面から楽曲タグ名で検索し、表示順や名前で並び替えながら一覧を確認できる状態を作る。既存の `Creator` / `Performer` / `Song` の検索機能と同じ利用感で、サーバー API と admin BFF / UI が一貫して動作するようにする。

## Scope

- `src/server/routes/admin.php` に `song-tags` の検索エンドポイントを配線し、`src/server/app/Http/Controllers/Api/SongTag` と `src/server/app/Http/Presenters/Api/SongTag` に検索 API の HTTP 入口とレスポンス整形を追加する
- `src/server/packages/Song/Application/UseCase/SearchTag`、`src/server/packages/Song/Application/Interactors`、`src/server/packages/Song/Domain/Models/Tag`、`src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php` に、楽曲タグ検索の入力・認可・検索・ページング処理を実装する
- `src/server/tests/Feature/Api/SongTag` と必要な Integration / Support 層に、楽曲タグ検索 API の正常系と検索条件の検証を追加する
- `src/admin/src/server/index.ts` と `src/admin/src/server/routes` に `song-tags` BFF ルートを追加し、生成済み `songTagServiceSearchSongTags` を使って管理画面から検索 API を呼べるようにする
- `src/admin/src/pages/song-tags/index.astro`、`src/admin/src/components/song-tag/SearchList.tsx`、必要なら `src/admin/src/components/Sidebar.tsx` に一覧ページと導線を追加し、クエリ文字列同期・ソート・ページングを既存一覧 UI と同じパターンで提供する

## Non-Scope

- `src/contracts/src/admin/song-tags` や生成済み OpenAPI / SDK の契約変更
- 楽曲タグの作成・更新・削除機能、および詳細編集画面の追加
- 楽曲とタグの関連付け検索や、楽曲一覧側へのタグ絞り込み追加
- 閲覧サイト `src/viewer` への機能追加

## Acceptance Criteria

- 認証済み管理画面ユーザーが `/song-tags` で楽曲タグ一覧画面を開くと、既存データを取得して表示できる
- 画面上でタグ名を指定して検索すると、部分一致または既存契約に沿った name 条件で結果が絞り込まれ、0 件時は空結果を表示できる
- 画面上で `order_no` / `name` のソートと `asc` / `desc` の並び順、`page` / `per_page` の切り替えができ、URL クエリと表示状態が同期する
- `GET /admin/v1/song-tags/search` が認証・認可を通した上で `tags` と `maxPage` を返し、既存の `Creator` / `Performer` 検索 API と同等のページング挙動を持つ
- サーバー側 Feature Test と、必要な admin 側の lint / build 相当の確認で、追加した楽曲タグ検索の導線とレスポンス処理が壊れていないことを確認できる

## Steps

1. ✅ `src/server/packages/Song/Application/UseCase/SearchTag/*` と `src/server/packages/Song/Application/Interactors/SearchTagInteractor.php` を追加し、`SongTagSearchCriteria` を組み立てて認証・認可・検索結果返却を行う use case を定義する。
2. ✅ `src/server/packages/Song/Domain/Models/Tag/SongTagRepositoryInterface.php` と `src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php` に `search` / `maxPage` / `all` を実装し、`App\Models\Song\SongTag` を `name_lower` 前方一致 + sort/order + offset/limit で取得できるようにする。
3. ✅ `src/server/app/Http/Controllers/Api/SongTag/SearchSongTagController.php`、`src/server/app/Http/Presenters/Api/SongTag/{Converter,SearchPresenter}.php`、`src/server/routes/admin.php`、`src/server/app/Providers/Domain/SongServiceProvider.php` を追加・更新し、`GET /admin/v1/song-tags/search` を OpenAPI 契約どおりに配線する。
4. ✅ `src/server/tests/Feature/Api/SongTag/SearchSongTagTest.php` と必要な `Tests\Support\Domain\{EntityFactory,EntityStore}` を更新し、一覧取得・名前検索・0 件・ソート/ページングの代表ケースを通して API の振る舞いを固定する。
5. ✅ `src/admin/src/server/routes/song-tags.ts` を追加し、`src/admin/src/server/index.ts` に登録して、生成済み `songTagServiceSearchSongTags` を使う BFF `/api/song-tags/search` を実装する。
6. ✅ `src/admin/src/components/song-tag/SearchList.tsx` を追加し、`src/admin/src/pages/song-tags/index.astro` から読み込んで、`name` / `sort` / `order` / `page` / `per_page` を URL 同期する検索一覧 UI を実装する。
7. ✅ `src/admin/src/components/Sidebar.tsx` を更新して `/song-tags` への導線を追加し、既存の `creators` / `performers` 一覧と同じ操作感で画面遷移できるようにする。
8. `mise run test -- src/server/tests/Feature/Api/SongTag/SearchSongTagTest.php`、`bunx tsc --noEmit` など変更範囲に対応する確認を実行し、必要に応じて exec plan の Validation に結果を記録する。

## Decision Log

- 2026-04-29: server 側の検索実装は `Creator` / `Performer` の `SearchInteractor + Repository + Presenter` パターンに合わせ、`Song` 本体の `QueryService` ではなく `SongTagRepositoryInterface` 直結で実装する。タグは単一テーブル検索で join を必要とせず、既存の `SongTagRepository` も同責務に置かれているため。
- 2026-04-29: 名前検索は issue の「既存契約に沿った name 条件」を優先し、`CreatorRepository` / `PerformerRepository` と同じ `name_lower` 前方一致を基本方針にする。Acceptance Criteria の「部分一致」は実装確認時にずれがあれば issue 側で再合意する。
- 2026-04-29: admin 一覧 UI は `src/admin/src/components/creator/SearchList.tsx` と `performer/SearchList.tsx` を踏襲し、一覧専用の `song-tag/SearchList.tsx` を新設する。`songs` 一覧ほど追加マスタ取得は不要なので、BFF も検索 API の単純プロキシに留める。
- 2026-04-29: Validation は server の Feature Test を主、admin は型検査を最低ラインとして扱う。現時点で song-tag 一覧向けの専用 frontend test 基盤は見当たらないため、既存運用に合わせて `tsc` / 必要なら lint で回帰確認する。

## Validation

- `mise run test -- src/server/tests/Feature/Api/SongTag/SearchSongTagTest.php`
- `bunx tsc --noEmit`
- 必要に応じて `mise run test` または admin 側の lint コマンドを追加実行し、変更が広がった場合のみ対象を拡張する
- 2026-04-29: `bunx tsc --noEmit` は `src/admin` で再実行し成功。検索画面と BFF の query 型を生成 SDK の union に揃えた
- 2026-04-29: `docker exec isekai-observatory-song-tag-search-ea3b34a1-php-1 php artisan test tests/Feature/Api/SongTag/SearchSongTagTest.php` が成功（4 tests, 8 assertions）
- 2026-04-29: `src/server/.env.testing` の設定で atlas testing schema を適用し、`song_tags` を含むテスト用テーブル不足を解消した
