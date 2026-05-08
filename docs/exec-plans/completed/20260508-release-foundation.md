# Title

Release 基盤

## Status

completed

## Background

`docs/product-specs/20260503-song-media-admin-phase1/README.md` と `models.md` では、Phase 1 の対象として `Release` とその収録曲が定義されている。一方で、現行コードベースには `Media` と `SongMediaLink` の server 実装はあるが、`Release` は `src/viewer` のモック表示に留まり、`src/server` / `src/admin` / `src/contracts` に後続実装の共通依存先となる基盤がまだない。

並列開発を始める前に、変更衝突が起きやすい API 契約や管理画面には入らず、server 側で静的な土台だけを先に固定する必要がある。

## Goal

`Release` と `TrackEntry` を表現するための最小限の server 基盤を追加し、後続の API、repository、admin 実装が並列で依存できる共通境界を整える。

## Scope

- `src/server/database/atlas/schemas` に `releases` / `release_track_entries` のテーブル定義を追加する
- `src/server/database/atlas/atlas.hcl` に新規スキーマを登録する
- `src/server/app/Models/Release` に、後続の永続化実装が依存できる Eloquent Model を追加する
- `src/server/packages/Release/Domain/Models` を追加し、`Release` / `TrackEntry` の pure domain model を定義する
- `src/server/composer.json` に `Release\\` の PSR-4 autoload を追加する

## Non-Scope

- `src/contracts` の TypeSpec 追加・更新
- OpenAPI や generated code の更新
- `src/server/packages/Release/Application` / `Infrastructures` / `Route` の実装
- `src/server/app/Http` の controller / presenter / request 実装
- `src/admin` の BFF、画面、フォーム実装
- `src/viewer` のモック置換や表示改善
- `Media` や `Song` の既存ユースケースの振る舞い変更
- `src/server/app/Models/Song` への relation 追加
- `src/server/packages/Song/Domain/Models/Song.php` に `Release` / `TrackEntry` を保持させる変更

## Acceptance Criteria

- `src/server/database/atlas/schemas` に `releases` と `release_track_entries` を表すファイルが追加され、`atlas.hcl` から参照されている
- テーブル定義は `docs/product-specs/20260503-song-media-admin-phase1/models.md` の最小モデルに沿って、`Release` の基本属性と `TrackEntry` の関連・曲順だけを持つ
- `src/server/app/Models/Release` と `src/server/packages/Release/Domain/Models` に、後続実装が参照できる `Release` / `TrackEntry` の型が追加されている
- 既存の `src/contracts`、`src/admin`、`src/server/app/Http`、`src/server/packages/*/Application` には変更が入っていない
- `src/server/app/Models/Song` には変更が入っていない
- `src/server/packages/Song/Domain/Models/Song.php` は `Release` / `TrackEntry` を持たないままである
- この基盤追加だけでは `Release` CRUD や画面導線はまだ提供されない

## Steps

1. ✅ `docs/product-specs/20260503-song-media-admin-phase1/models.md` の `Release` / 収録曲定義と、既存の `src/server/database/atlas/schemas/songs.my.hcl` / `song-media-links.my.hcl` / `src/server/app/Models/Media/Media.php` を突き合わせ、今回固定する最小カラムと relation 境界を確定する。ここでは `Song` は pure domain で `Release` も `TrackEntry` も持たず、関係は `Release` 側と read model 組み立て側に閉じる前提を計画の基準にする。
2. ✅ `src/server/database/atlas/schemas/releases.my.hcl` を新規追加し、`release_id`、`title`、`type`、`distribution_type`、`released_on`、`description`、`is_display`、`created_at`、`updated_at` を定義する。`models.md` の最小モデルに合わせつつ、Phase 1 対象外のジャケット画像や版管理カラムは入れない。
3. ✅ `src/server/database/atlas/schemas/release-track-entries.my.hcl` を新規追加し、`release_id`、`song_id`、`track_no` を持つ複合主キー付き中間テーブルを定義する。`releases` と `songs` への外部キー、曲順、同一楽曲の複数リリース所属をこのテーブル責務に閉じ込める。
4. ✅ `src/server/database/atlas/atlas.hcl` の `table_schemas` に `releases.my.hcl` と `release-track-entries.my.hcl` を追加し、DB Source of Truth を Atlas に集約する。
5. ✅ `src/server/app/Models/Release/Release.php` と `src/server/app/Models/Release/TrackEntry.php` を追加し、既存の `App\Models\Song` / `App\Models\Media` と同じ Eloquent パターンで primary key、casts、必要最小限の relation を定義する。`Song` 側には relation を追加せず、`TrackEntry` は `Release` 側からのみ参照できる形に留める。
6. ✅ `src/server/packages/Release/Domain/Models` を新設し、少なくとも `Release.php`、`ReleaseId.php`、`ReleaseTitle.php`、`ReleaseType.php`、`ReleaseDistributionType.php`、`ReleasedOn.php`、`Description.php`、`TrackEntry.php`、`TrackEntries.php` を追加する。既存の `Media` / `Song` domain model に寄せて `reconstruct()` と `toArray()` を用意し、後続の repository・application 実装が依存できる pure domain の型を先に固定する。
7. ✅ `src/server/composer.json` の PSR-4 autoload に `Release\\` を追加し、新規 package の namespace 解決を有効にする。必要であれば既存 package 名との衝突がないことも同時に確認する。
8. ✅ 実装後は差分を確認し、変更が `src/server/database/atlas`、`src/server/app/Models/Release`、`src/server/packages/Release`、`src/server/composer.json` に閉じていること、`src/server/app/Models/Song` や application / contract / admin に差分がないことを検証する。

## Decision Log

- 2026-05-08: `Song` の pure domain model には `Release` も `TrackEntry` も持たせず、`Song` と `Release` の関係は `TrackEntry` テーブルと後続の read model 組み立てでのみ表現する。集約責務の混在を避けるため。
- 2026-05-08: 今回の基盤追加では `src/server/app/Models/Song` に relation を追加しない。並列実装時に `Song` 取得系の競合を増やさず、静的な依存先だけを先に固定するため。
- 2026-05-08: `Release` 側の Eloquent / domain model だけを先に追加し、repository・application・HTTP・admin は後続タスクに分離する。変更面を局所化し、API / UI 実装と独立に進められるようにするため。
- 2026-05-08: `releases` テーブルは product spec の最小属性に留め、`distribution_type` と `released_on` を単数の属性として保持する。版違いは別 `Release` として登録し、ジャケット画像や版管理までは扱わないため。
- 2026-05-08: `release_track_entries` は `release_id` / `song_id` / `track_no` のみを持つ単純な中間テーブルとし、将来の disc number などは今回入れない。後方互換を壊さずに拡張できる余地を残しつつ、初期基盤を最小に保つため。
- 2026-05-08: 流通形態は複数値ではなく `ReleaseDistributionType` の単一 enum として扱う。版違いを別 `Release` に分ける運用の方針と整合し、後続の API / admin 実装も単純になるため。
- 2026-05-08: `release_track_entries` では `release_id + track_no` を unique にして、同一リリース内で曲順が重複しないことを DB で保証する。後続の read model や UI が曖昧な並び順を扱わずに済むようにするため。
- 2026-05-08: 発売日は `ReleaseDate` ではなく `ReleasedOn` と命名し、日付の意味を「いつ発売されたか」に固定する。後続で公開開始日や予約開始日など別の日付が増えても衝突しにくくするため。

## Validation

- `src/server/database/atlas/atlas.hcl` に `releases.my.hcl` と `release-track-entries.my.hcl` が追加され、`src/server/database/atlas/schemas` 側に対応ファイルが存在することを確認する。
- `releases` テーブルが `release_id` / `title` / `type` / `distribution_type` / `released_on` / `description` / `is_display` / timestamp を持ち、`release_track_entries` テーブルが `release_id` / `song_id` / `track_no` と外部キーを持つことを確認する。
- `release_track_entries` に `release_id + track_no` の unique index があり、同一リリース内で曲順重複を防げることを確認する。
- `src/server/app/Models/Release/Release.php` と `src/server/app/Models/Release/TrackEntry.php`、`src/server/packages/Release/Domain/Models/*` が追加され、既存モデルと同等の namespace / reconstruct / `toArray()` パターンに沿っていることを確認する。
- `src/server/composer.json` に `Release\\` の PSR-4 autoload が追加され、新規 domain model の namespace 解決ができる状態になっていることを確認する。
- `src/server/app/Models/Song`、`src/contracts`、`src/server/app/Http`、`src/server/packages/*/Application`、`src/admin` に差分がないことを `git diff --name-only` で確認する。
- `src/server/packages/Song/Domain/Models/Song.php` に差分がなく、`Song` が `Release` / `TrackEntry` を保持しない前提が維持されていることを確認する。
- 必要に応じて `mise run api:phpstan` の対象を新規 package / model に絞って実行し、少なくとも追加した PHP ファイル群に静的解析エラーがないことを確認する。
