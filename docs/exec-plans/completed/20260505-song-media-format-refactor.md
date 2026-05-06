## Title

Song Media Format Refactor

## Status

completed

## Background

現状の `Media` 実装では、`song_media_links.song_media_type` が `MV` / `音源動画` / `配信アーカイブ` / `ショート動画` のような表示分類を保持している。この構造だと、同じ `media_id` を複数楽曲へ紐づけたときに楽曲ごとに異なる種別を持ててしまい、コンテンツ自体の意味が揺れる。

一方で、複数楽曲が同じ `Media` を参照しつつ、楽曲ごとに表示順だけは変えたい要件がある。また将来的に `Event` など他集約からも `Media` を参照したい。

このため、`Media` 自体に `format` を持たせ、`song_media_links` は楽曲との関連と表示順だけを持つ構造へ寄せる。

## Goal

`Media` の意味分類を `media.format` に集約し、`Song` 側では `Media` の並び順だけを管理する構造へ整理する。これにより、同一 `Media` を複数楽曲で共有しつつ、分類は共通化し、表示順は楽曲ごとに変更できる状態にする。

## Scope

- `src/contracts/src/admin/media/*`
- `src/contracts/src/admin/songs/*`
- `src/server/database/atlas/schemas/media.my.hcl`
- `src/server/database/atlas/schemas/song-media-links.my.hcl`
- `src/server/packages/Media/*`
- `src/server/packages/Song/*`
- `src/server/app/Http/Presenters/Api/Media/*`
- `src/server/app/Http/Presenters/Api/Song/Converter.php`
- `src/admin/src/server/routes/media.ts`
- `src/admin/src/server/routes/songs.ts`
- `src/admin/src/components/song/MediaSection.tsx`
- `src/admin/src/components/song/CreateForm.tsx`
- `src/admin/src/components/song/EditableForm.tsx`

## Non-Scope

- `EventMedia` の新設
- `viewer` 側の表示反映
- `Media` 一覧 / 編集画面の新設
- `Media` の provider / thumbnail / duration など追加メタデータの拡張

## Acceptance Criteria

- `Media` 契約に `format` が追加され、admin 生成物に反映されている
- `Song` 契約から `songMediaType` が除去され、`media` 関連 request は `mediaId` と `orderNo` のみを送る
- `song_media_links` は `order_no` のみを持つ構造へ移行される
- `Song` 作成 / 更新 / 取得で `Media.format` が返り、楽曲文脈種別 UI は消える
- Song フォーム内の新規 Media 作成 UI で `format` を選択できる
- 同一 `Media` を複数楽曲へ紐づけても、各楽曲で並び順を独立して保持できる

## Steps

1. `Media.format` を契約へ追加する
   `src/contracts/src/admin/media/domain.tsp` に `MediaFormatValue` と `MediaFormat` もしくは `format` 用 enum を追加し、`Media` に `format` を持たせる。あわせて Song 返却で使う `SongLinkedMedia` も `Media` の `format` を返す形へ揃える。

2. `Song` 契約から楽曲文脈種別を除去する
   `src/contracts/src/admin/songs/domain.tsp` から `SongMediaType` を削除し、`RequestSongMediaLink` を `mediaId` と `orderNo` のみにする。`SongLinkedMedia` からも `songMediaType` を外す。

3. DB スキーマと server ドメインを `format + order_no` 構造へ寄せる
   `src/server/database/atlas/schemas/media.my.hcl` に `format` を追加し、`song-media-links.my.hcl` から `song_media_type` を削除する。`Media` ドメインモデル / repository / presenter を `format` 対応にし、`SongMediaLink` / `SongMediaLinks` は `media_id` と `order_no` だけを扱うよう整理する。

4. `Song` の create / get / update / assembler / repository を簡素化する
   `SongIntegrityService`、`CreateInputData`、`UpdateInputData`、`SongRepository`、`SongAssembler`、`Song` presenter を更新し、楽曲側では Media の存在確認と順序管理だけを行う。返却時の表示分類は `Media.format` から組み立てる。

5. admin BFF と Song フォームを `Media.format` 基準へ更新する
   `src/admin/src/server/routes/media.ts` の新規作成受け口へ `format` を追加する。`songs.ts` は `songMediaType` を送らない形へ更新する。`MediaSection.tsx` から楽曲文脈種別の選択 UI を削除し、新規 Media 作成フォームへ `format` 選択を追加する。

6. 既存データ移行方針を定めて migration / 生成物 / テストを更新する
   既存の `song_media_links.song_media_type` を `media.format` へ移す方針を決める。原則は、同一 `media_id` に複数の `song_media_type` が存在しない前提で移行し、衝突があれば手動確認とする。生成物更新後、Song / Media の feature test と admin の lint / typecheck を通す。

## Decision Log

- `Media.type` は媒体種別 (`video` / `article` / `social_post` / `official_page` / `other`) として維持する
- `Media.format` はコンテンツ表現分類 (`mv` / `audio_video` / `stream_archive` / `short_video` / `teaser` / `live_clip` / `other`) とする
- `song_media_links` は楽曲との関連と `order_no` のみを持つ
- 同一 `Media` を複数楽曲で共有することは許可し、並び順のみ楽曲ごとに持つ
- 配信アーカイブ内で複数楽曲が披露された事実は、複数楽曲が同じ `Media` を参照することで表現する

## Validation

- `mise run contract:test`
- `mise run contract:compile:admin`
- `mise run admin:generate`
- `mise run api:generate`
- `docker compose exec php php artisan test tests/Feature/Api/Song tests/Feature/Api/Media`
- `cd src/admin && bun run lint:check src/components/song src/server/routes src/server/index.ts`
- `cd src/admin && bunx tsc --noEmit -p tsconfig.json`
- 必要なら schema migration を local / testing へ適用して create / update / get の手動確認
