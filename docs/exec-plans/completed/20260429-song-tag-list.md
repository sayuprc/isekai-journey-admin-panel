# Title

楽曲タグ一覧機能

## Status

completed

## Background

`src/contracts/src/admin/song-tags/service.tsp` と生成済み OpenAPI / Admin SDK には `listSongTags` 契約があるが、`src/server` には `song-tags` 一覧 API の route・controller・presenter・use case 実装がなく、`src/admin` にも一覧ページが存在しない。現状の管理画面では楽曲種別・楽曲属性は一覧できる一方で、楽曲タグだけ参照導線が欠けており、登録済みタグの確認や並び順の把握ができない。

## Goal

既存の `listSongTags` 契約を実装に接続し、サーバー API と Admin BFF から楽曲タグ一覧を取得できる状態にする。少なくとも認証済み管理ユーザーの文脈で `/admin/v1/song-tags` を呼べる BFF 入口を揃える。

## Scope

- `src/server/routes/admin.php` に `song-tags` 一覧 route を追加する
- `src/server/app/Http/Controllers/Api/SongTag` と `src/server/app/Http/Presenters/Api/SongTag` に一覧 API の受け口を追加する
- `src/server/packages/Song/Application` に楽曲タグ一覧 use case / interactor / output data を追加し、`src/server/app/Providers/Domain/SongServiceProvider.php` で解決できるようにする
- `src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php` で一覧取得を実装する
- `src/server/tests/Feature/Api/SongTag/ListSongTagTest.php` など、一覧 API の振る舞いを検証するテストを追加する
- `src/admin/src/server/routes/song-tags.ts` と `src/admin/src/server/index.ts` に Admin BFF の一覧入口を追加する

## Non-Scope

- `src/contracts/src/admin/song-tags` や生成済み OpenAPI / SDK の契約変更
- 楽曲タグの検索・作成・更新・削除 API と編集 UI の実装
- 楽曲とタグの関連付け (`song_taggings` など) の実装や仕様変更
- 管理画面のサイドバーやグローバルナビゲーションへの導線追加
- Astro ページや一覧 UI の追加

## Acceptance Criteria

- 認証済みで `GET /admin/v1/song-tags` を呼ぶと、`order_no` 昇順の `tags` 配列が 200 で返り、各要素に `songTagId`・`name`・`orderNo` が含まれる
- 未認証で `GET /admin/v1/song-tags` を呼ぶと、既存の管理 API と同様に認証エラーになる
- Admin BFF の `GET /song-tags` からサーバー API の一覧結果を取得できる
- 一覧 API の Feature Test が追加され、タグが複数件あるとき返却順とレスポンス形が検証されている

## Steps

1. ✅ `src/server/packages/Song/Domain/Models/Tag/SongTagRepositoryInterface.php` に一覧取得メソッドを定義し、`src/server/packages/Song/Application/UseCase/ListTag/` と `src/server/packages/Song/Application/Interactors/ListTagInteractor.php` を追加して、楽曲タグ一覧を返す use case の入出力を構成する。
2. ✅ `src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php` に `App\Models\Song\SongTag` を使った一覧取得を実装し、`order_no` 昇順で取得したレコードを `Song\\Domain\\Models\\Tag\\SongTag::reconstruct()` に変換して返す。
3. ✅ `src/server/app/Providers/Domain/SongServiceProvider.php` に `ListTagUseCaseInterface` と `ListTagInteractor` の bind を追加し、既存の `registerSongType()` / `registerSongAttribute()` と同じ責務分割で `song-tags` 用の登録処理を追加する。
4. ✅ `src/server/app/Http/Controllers/Api/SongTag/ListSongTagController.php`、`src/server/app/Http/Presenters/Api/SongTag/{Converter,ListPresenter}.php` を追加し、use case の結果を `SongTagListResponse` へ変換して 200 / エラー JSON を返せるようにする。
5. ✅ `src/server/routes/admin.php` に `song-tags` 一覧 route を追加し、`Song\\Route\\Tag\\SongTagRouteMap::List` と新設 controller を紐付けて、既存の認証ミドルウェア配下で `GET /admin/v1/song-tags` を公開する。
6. ✅ `src/server/tests/Feature/Api/SongTag/ListSongTagTest.php` を追加し、複数タグの seed 後に認証済みリクエストで返却順・レスポンス形を検証し、未認証時の認証エラーも確認する。
7. ✅ `src/admin/src/server/routes/song-tags.ts` を追加し、既存の `song-types` / `song-attributes` BFF と同じく `authGuard` 配下で server client の `songTagServiceListSongTags()` を呼び出してレスポンスを返す。
8. ✅ `src/admin/src/server/index.ts` に `song-tags` BFF ルートを登録し、Admin 側から `GET /song-tags` を叩けるようにする。

## Decision Log

- 2026-04-29: `listSongTags` は既存契約と生成済み SDK / OpenAPI が揃っているため、契約変更は行わず、サーバー実装と管理画面接続だけを追加する。
- 2026-04-29: `song-types` / `song-attributes` は enum ベースの静的一覧だが、`song-tags` は `song_tags` テーブルを持つため、一覧 use case は repository 経由で DB から取得する構成にする。
- 2026-04-29: レスポンス整形は他 API と同じく presenter + converter に閉じ込め、controller は `handle()` で use case と presenter を接続するだけの薄い責務に保つ。
- 2026-04-29: Admin 側は Astro ページを作らず、既存の Elysia BFF ルート追加に限定する。UI や導線は別タスクに分離する。
- 2026-04-29: `song_tags.song_tag_id` は `binary(16)` のため、一覧 repository では `UuidConverterInterface` を使って DB 値を UUID 文字列へ戻し、API 応答の契約と一致させる。

## Validation

- Acceptance Criteria 1: `ListSongTagTest` で複数タグを `order_no` が前後する状態で保存し、`GET /admin/v1/song-tags` の 200 応答が `tags` 配列を `orderNo` 昇順で返し、各要素に `songTagId`・`name`・`orderNo` が含まれることを `assertExactJson` で検証する。
- Acceptance Criteria 2: `ListSongTagTest` で認証なしの `GET /admin/v1/song-tags` を実行し、既存管理 API と同じ認証エラーのステータスになることを検証する。
- Acceptance Criteria 3: `src/admin/src/server/routes/song-tags.ts` と `src/admin/src/server/index.ts` に BFF ルートが追加され、認証済み文脈で `GET /song-tags` が server client の `songTagServiceListSongTags` を通して一覧結果を返すことをコードと必要なテストで確認する。
- Acceptance Criteria 4: 実装後に `ListSongTagTest` を実行し、返却順・レスポンス形・認証要件が自動テストで担保されていることを確認する。
