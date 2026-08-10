# Title

楽曲タグ API の共通基盤整備

## Status

 completed

## Background

`src/contracts/src/admin/song-tags` と生成済み OpenAPI には `song-tags` API の契約が存在するが、`src/server` 側にはそれを受ける共通基盤がまだない。現状は `src/server/routes/admin.php` に `song-tags` ルートがなく、`SongType` / `SongAttribute` と同じく `Song` パッケージ配下にドメイン実装を置きつつ、API 入口だけ `SongTag` 名前空間として切る構成も未整備である。さらに、Atlas のテーブル定義や `app/Models` の `SongTag` 関連 Model も存在しない。このままでは一覧・検索・作成・更新・削除 API を並列実装するための入口と依存解決、永続化の土台がなく、作業が各所で衝突しやすい

## Goal

`song-tags` 契約を受け止めるサーバー側の共通基盤を先に揃え、`SongType` / `SongAttribute` と同様にドメインは `Song` パッケージへ集約しつつ、API 入口は `SongTag` 名前空間で統一した状態で複数 API を並列実装できるようにする

## Scope

- `src/server/packages/Song/{Domain,Infrastructures,Route}` に `SongTag` 用の共通土台を追加し、後続 API 実装が `Song` パッケージ内で同じモデル・interface・空の実装・ルート名を共有できるようにする
- `src/server/app/Providers/Domain/SongServiceProvider.php` に `SongTag` の binding を追加し、後続タスクが共通 interface をそのまま注入できるようにする
- `src/server/database/atlas/atlas.hcl` と `src/server/database/atlas/schemas/*.my.hcl` に `song-tags` および必要なら関連テーブルのスキーマ定義を追加し、`Song` ドメイン配下の永続化前提を Atlas で管理できるようにする
- `src/server/app/Models/Song` に `SongTag` および必要な関連 Model を追加し、後続 API 実装が既存の Eloquent 利用パターンに沿って永続化へアクセスできるようにする

## Non-Scope

- `src/contracts/src/admin/song-tags` や `src/contracts/generated/oas` の契約変更
- `src/server/app/Http/Controllers/Api/SongTag`、`src/server/app/Http/Presenters/Api/SongTag`、`InputData` / `Interactor` / `UseCaseInterface` など各 API 個別実装の受け口作成
- `src/admin` 側の画面実装や BFF 実装

## Acceptance Criteria

- `song-tags` のドメイン実装は `src/server/packages/Song` 配下に置かれ、後続 API 実装が共有できる `SongTag` のモデル・値オブジェクト・空の interface・検索条件などの共通型が定義されている
- `src/server/packages/Song/Infrastructures/Tag` に空の `SongTagFactory` / `SongTagRepository` があり、`src/server/app/Providers/Domain/SongServiceProvider.php` に対応 binding が追加されている
- `src/server/packages/Song/Route/SongTagRouteMap.php` に契約済みの list/search/create/update/delete エンドポイント名が定義され、後続タスクが同じ route 名を共有できる
- `src/server/database/atlas/atlas.hcl` と追加した `song-tags` 系スキーマ定義から、楽曲タグ API 実装に必要なテーブル構造を管理できる
- `src/server/app/Models/Song` に追加した `SongTag` 関連 Model が、既存の `Song` / `Creator` / `Performer` Model と同じ薄い Eloquent モデルの責務で定義されている
- 今回の変更だけで `song-tags` API 実装担当が DB・Eloquent Model・Domain 契約・Route 名を共有しながら、HTTP 層や UseCase 実装を別タスクで並列着手できる

## Steps

1. ✅ `src/server/database/atlas/atlas.hcl` に `song-tags` 用 schema ファイルを追加し、`src/server/database/atlas/schemas/song-tags.my.hcl` を新設して `song_tags` テーブル定義を追加する。列は契約と生成済み OpenAPI に合わせて最低限 `song_tag_id`, `name`, `name_lower`, `order_no`, `created_at`, `updated_at` を持たせ、検索で必要な lower-case index と表示順用 index の有無もここで既存マスタと比較して決める
2. ✅ `src/server/app/Models/Song/SongTag.php` を追加し、既存 `App\Models\Creator\Creator` / `App\Models\Performer\Performer` と同じ薄い Eloquent Model として primary key, key type, casts だけを定義する。`song-tags` 共通基盤の対象を CRUD 本体に必要な `song_tags` マスタへ絞り、`song_taggings` のような関連テーブルは今回の Scope 外として追加しない
3. ✅ `src/server/packages/Song/Domain` に `SongTag` 集約の基礎型を追加する。少なくとも `Domain/Models/Tag/SongTag.php`, `SongTagId.php`, `SongTagName.php`, `SongTagFactoryInterface.php`, `SongTagRepositoryInterface.php` と、検索 API の入口で共有する `Domain/Criteria/Tag/SongTagSearchCriteria.php`, `SongTagSort.php` を追加し、後続タスクが同じ型を前提に実装できるようにする。interface は marker として置き、メソッド定義はこのタスクでは持たない
4. ✅ `src/server/packages/Song/Infrastructures/Tag` に空の `SongTagFactory.php` と `SongTagRepository.php` を追加し、interface の受け皿だけを先に用意する。永続化ロジックや factory ロジックの本実装はこのタスクでは持たない
5. ✅ `src/server/packages/Song/Route/Tag/SongTagRouteMap.php` を追加し、後続タスクが共有する `List`, `Search`, `Create`, `Update`, `Delete` の route 名を定義する。`routes/admin.php` や Controller 実装はこのタスクでは追加せず、契約済み API 名称の共通定数だけを先に揃える
6. ✅ `src/server/app/Providers/Domain/SongServiceProvider.php` に `SongTagFactoryInterface` / `SongTagRepositoryInterface` の binding を追加し、後続タスクが空の実装を経由して interface 注入を始められるようにする
7. ✅ 実装後は `mise run phpstan` と `mise run ecs:fix` または同等の server 側静的検査を実行し、必要なら Atlas schema 差分を確認する。差分確認では `docs/exec-plans/active/20260429-song-tag-api-common-foundation.md` を除き、変更が `src/server/app/Models`, `src/server/packages/Song`, `src/server/database/atlas`, `src/server/app/Providers/Domain/SongServiceProvider.php` に閉じていることを確認する

## Decision Log

- 2026-04-29: 実装順は HTTP 入口からではなく、Atlas schema と Eloquent Model を先に整えてから Domain / Route 定数の順にする。後続の API 個別実装がこれらの土台に依存するため
- 2026-04-29: `song-tags` のドメイン実装は `src/server/packages/Song` に集約し、`SongTag` 専用 package や service provider は追加しない。Issue の Goal と Acceptance Criteria を優先し、既存 `SongType` / `SongAttribute` の配置ルールに合わせる
- 2026-04-29: 今回の共通基盤では `song_tags` マスタを対象にし、`song_taggings` など楽曲との関連テーブルまでは広げない。CRUD API の受け口と永続化土台を先に安定させ、関連付けは別タスクへ分離する
- 2026-04-29: `InputData`、Interactor、UseCaseInterface、Controller、Presenter、Feature Test は API ごとの差分が大きく別タスクで並列に持てるため、この共通基盤タスクには含めない
- 2026-04-29: Route については `routes/admin.php` の配線までは行わず、`SongTagRouteMap` の共通定数だけを先に固定する。ルート配線は実際の Controller 実装タスクで追加する
- 2026-04-29: Infrastructure の本実装は API ごとの差分を先に凍らせやすいため、この共通基盤タスクには含めない。一方で interface と空の実装、自動解決用 binding 自体は後続タスクの配置目印として有効なので残す

## Validation

- `mise run phpstan` を実行し、`Song` package 配下の新規型、空の Infrastructure 実装、`App\Models\Song\SongTag`、RouteMap、`SongServiceProvider` の binding 追加に静的解析エラーがないことを確認する
- `mise run ecs:fix` もしくは同等の code style コマンドを実行し、新規 Model / Domain / Infrastructure / RouteMap ファイルが既存 server コード規約に従うことを確認する
- Atlas schema 差分を確認し、`song_tags` 以外の既存テーブル定義を意図せず変更していないことを確認する
