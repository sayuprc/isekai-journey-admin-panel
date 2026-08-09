# Title

ジャケットアートの残骸を掃除する

## Status

completed

## Background

1 本目で `jacket_art_url` を `color` へ置き換え、2〜4 本目で viewer の見た目を作り直した。
この計画で、参照されなくなった定義とストレージの実ファイルを落とす。

**ストレージの削除は不可逆**である。そのため 5 本の最後に置き、前の 4 本がマージされてから着手する。

## Goal

ジャケット由来のコードと画像ファイルをリポジトリとストレージから消し、参照が 1 つも残らない状態にする。

## Scope

- `src/viewer/src/components/viewer/JacketArt.astro`
- `src/viewer/src/shared/image.ts`（ジャケット専用になったためファイルごと削除）
- `src/viewer/src/styles/viewer.css` の `.jacket-art` 系
- サーバーの `JacketArtStorage` 系（アップロード API 削除後の死コード）
- ストレージの `release-jacket-art/` 配下

## Non-Scope

- 色の抽出機能（将来別途）
- YouTube サムネイル（`media/[mediaId].astro` の OGP は変更しない）
- DB カラム（1 本目で置き換え済み）
- ADR / exec-plan 文書内の歴史的な `jacket` 表記

## Acceptance Criteria

- `src/` を `jacket` で検索して 1 件もヒットしない（生成コード・vendor 除く）
- ローカル MinIO の `release-jacket-art/` が空になっている
- viewer / server の静的検査が通る
- viewer の全ページが表示でき、画像は YouTube サムネイルだけになっている

## Steps

- [x] `JacketArt.astro` を削除する
- [x] `shared/image.ts` を削除し、Layout の assets preconnect も外す（利用者はジャケットだけだった）
- [x] `viewer.css` から `.jacket-art` 系のスタイルを削除する
- [x] サーバーの `JacketArtStorageInterface` / `R2JacketArtStorage*` と Provider 配線を削除する
- [x] ローカル MinIO の `release-jacket-art/` 配下を削除する
- [x] `src/` を `jacket` で検索し、残りが無いことを確認する

## Decision Log

共通の判断は `20260809-release-color-column.md` を参照。

- 2026-08-09: 不可逆な削除（ストレージ）はこの 1 本に隔離し、5 本の最後に置く
- 2026-08-09: ストレージの退避は行わない。本番のファイルは削除済みで、ローカルの MinIO に残っている 87 件は今回使わない
- 2026-08-09: `image.ts` はジャケット以外の利用者が無くなったためファイルごと削除する。YouTube サムネイルは `features/media/labels` 側で完結している
- 2026-08-09: アップロード API 削除で死んだ `JacketArtStorage` 系もこの掃除に含める。Acceptance の「jacket が残らない」をコード側で満たすため

## Validation

- `mise run viewer:check` / `mise run api:phpstan` 通過
- `bun --filter viewer build` 通過
- ローカル MinIO `release-jacket-art/` を 107 → 0 件にした
- `rg -i jacket src`（generated / vendor 除く）でヒットなし
