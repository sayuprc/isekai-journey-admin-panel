# Title

Song Media 基盤

## Status

 completed

## Background

`docs/product-specs/20260503-song-media-admin-phase1/README.md` と `models.md` では、Phase 1 の差別化要素として `Media` と `SongMediaLink` を既存の `Song` / `Person` / `SongTag` 基盤の上で運用する方針が定義されている。現状のコードベースには `songs`、`song_persons`、`song_tags`、`song_taggings` はあるが、`Media` の永続化構造や Entity がまだ存在しないため、後続の API / admin 実装を並列で進める共通土台が不足している。

既存構成では、API 契約は `src/contracts`、永続化は `src/server/database/atlas`、server 側のモデルは `src/server/app/Models` と `src/server/packages/*/Domain/Models` に分かれている。並列実装時の衝突を避けるには、まず Source of Truth のうち影響範囲が比較的局所的な DB スキーマと server 側 Entity 定義だけを先に追加し、contracts や controller、admin 画面への変更は後続タスクへ分離する必要がある。

## Goal

`Media` と `SongMediaLink` を表現するための最小限のテーブル定義と server 側 Entity 群を追加し、後続の API・repository・UI 実装が依存できる静的な基盤を整える。既存の `Song` 周辺の実行系には踏み込まず、並列作業しやすい変更単位に留める。

## Scope

- `src/server/database/atlas/schemas`
- `src/server/database/atlas/atlas.hcl`
- `src/server/app/Models/Song`
- `src/server/app/Models/Media`
- `src/server/packages/Media/Domain/Models`
- `src/server/composer.json`
- 既存 `Song` ドメインから新 relation を参照するために必要な最小限の `src/server/packages/Song/Domain/Models`

## Non-Scope

- `src/contracts` の TypeSpec 追加・更新
- OpenAPI / generated code の更新
- route / controller / presenter / provider / use case / repository の実装
- `admin` / `viewer` の UI・BFF 実装
- `Media` の CRUD や検索 API の詳細設計
- `Release` / `ReleaseSongLink` のテーブル定義、Entity、契約、実装
- `Song` 詳細レスポンスへの `Media` の組み込み
- Phase 2 以降の `Event` / `EventMediaLink` 対応

## Acceptance Criteria

- `src/server/database/atlas/schemas` に `media` と `song_media_links` を表すスキーマが追加され、`atlas.hcl` から参照されている
- 各テーブル定義は `docs/product-specs/20260503-song-media-admin-phase1/models.md` の最小モデルに沿って、Phase 1 で必要な識別子、基本属性、表示制御、順序、外部キーだけを持つ
- `src/server/app/Models` と `src/server/packages/*/Domain/Models` に、後続実装が参照できる `Media` / `SongMediaLink` の Entity が追加されている
- 既存の `Song` 契約、controller、use case、admin 画面には変更が入っていない
- `Release`、`Event`、`Media` 単独管理など今回の対象外の概念は、この基盤追加に含まれていない

## Steps

✅ 1. `docs/product-specs/20260503-song-media-admin-phase1/models.md` の `Media` / `SongMediaLink` 定義と、既存の `src/server/database/atlas/schemas/songs.my.hcl` / `song-persons.my.hcl` / `song-taggings.my.hcl` を突き合わせ、今回固定する最小カラムを確定する。ここでは `Media` を独立エンティティ、`SongMediaLink` を楽曲文脈の中間テーブルとして扱い、`Song` の既存実行系に必要以上の属性を持ち込まない。
✅ 2. `src/server/database/atlas/schemas/media.my.hcl` を新規追加し、`media_id`、`title`、`url`、`type`、`is_display`、`created_at`、`updated_at` を定義する。検索や将来の再利用を阻害しない最小インデックスだけを付け、`Release` / `Event` 前提のカラムは入れない。
✅ 3. `src/server/database/atlas/schemas/song-media-links.my.hcl` を新規追加し、`song_id`、`media_id`、`order_no` を持つ複合主キー付き中間テーブルを定義する。`songs` と `media` への外部キー、並び順を扱うためのカラム責務をここに閉じ込める。
✅ 4. `src/server/database/atlas/atlas.hcl` の `table_schemas` に新規スキーマファイルだけを追加し、DB Source of Truth の差分を Atlas 側に閉じる。
✅ 5. `src/server/app/Models/Media/Media.php` と `src/server/app/Models/Song/SongMediaLink.php` を追加し、既存の `Song` / `Person` モデルに合わせた Eloquent 定義を入れる。必要なら `src/server/app/Models/Song/Song.php` に `songMediaLinks()` の relation メソッドだけを追加するが、`$with` や既存取得挙動は変更しない。
✅ 6. `src/server/packages/Media/Domain/Models` を新設し、少なくとも `Media.php`、`MediaId.php`、`MediaTitle.php`、`MediaUrl.php`、`MediaType.php`、`SongMediaLink.php` を追加する。既存の `Song\Domain\Models` や `Person\Domain\Models` の value object / enum パターンに寄せ、後続の repository・use case が参照できる純粋な再構築 API と `toArray()` を用意する。あわせて `src/server/composer.json` に `Media\\` の autoload を追加する。
✅ 7. `Song` 側の pure domain model は原則変更しない。後続実装がどうしても参照起点を必要とする場合だけ、`src/server/packages/Song/Domain/Models` に relation 参照用の最小型を追加するが、`Song` コンストラクタや既存 use case のシグネチャ変更は避ける。
✅ 8. 実装後は差分を確認し、変更が `src/server/database/atlas`、`src/server/app/Models/Media`、`src/server/app/Models/Song`、`src/server/packages/Media`、必要最小限の `src/server/packages/Song/Domain/Models` に閉じていることを検証する。

## Decision Log

- 2026-05-05: `Release` と `ReleaseSongLink` は今回の Goal から完全に外し、`Media` / `SongMediaLink` の静的基盤だけに絞る。並列実装時の衝突面を減らすため。
- 2026-05-05: `SongMediaLink` の表示順は `song_media_links.order_no` に持たせ、`Media` 自体には楽曲依存の並び責務を持たせない。将来 `Event` など別文脈から `Media` を再利用しやすくするため。
- 2026-05-05: 既存 `Song` の Eloquent `$with`、pure domain model のコンストラクタ、use case / repository シグネチャはこのタスクでは変更しない。後続の API 実装と独立に進められる基盤に留めるため。
- 2026-05-05: `Media` package は Domain Models のみを先行追加し、repository interface や application 層はまだ作らない。静的な依存先だけを先に固定するため。
- 2026-05-05: `media` テーブルには主キー以外の追加 index を持たせず、`song_media_links` は `media_id` 外部キー成立に必要な index のみを追加する。今回の基盤追加を最小差分に留めるため。
- 2026-05-05: `packages/Media` を新設するため、`src/server/composer.json` の PSR-4 autoload に `Media\\` を追加する。後続実装が新規 domain model をそのまま参照できる状態を先に整えるため。

## Validation

- `src/server/database/atlas/atlas.hcl` に `media.my.hcl` と `song-media-links.my.hcl` が追加され、`src/server/database/atlas/schemas` 側に対応ファイルが存在することを確認する。
- `media` テーブルが `media_id` / `title` / `url` / `type` / `is_display` / timestamp を持ち、`song_media_links` テーブルが `song_id` / `media_id` / `order_no` と外部キーを持つことを `docs/product-specs/20260503-song-media-admin-phase1/models.md` と照合して確認する。
- `src/server/app/Models/Media/Media.php`、`src/server/app/Models/Song/SongMediaLink.php`、`src/server/packages/Media/Domain/Models/*` が追加され、既存モデルと同等の namespace / reconstruct / `toArray()` パターンに沿っていることを確認する。
- `src/server/composer.json` に `Media\\` の PSR-4 autoload が追加され、新規 domain model の namespace 解決ができることを確認する。
- `src/contracts`、`src/server/app/Http`、`src/server/packages/*/Application`、`src/admin` に差分がないことを `git diff --name-only` で確認する。
- 必要に応じて `mise run api:phpstan` の対象を新規 package / model に絞って実行し、少なくとも追加した PHP ファイル群に静的解析エラーがないことを確認する。
