# Title

ジャケットアートの残骸を掃除する

## Status

planned

## Background

1 本目で `jacket_art_url` を `color` へ置き換え、2〜4 本目で viewer の見た目を作り直した。
この計画で、参照されなくなった定義とストレージの実ファイルを落とす。

**ストレージの削除は不可逆**である。そのため 5 本の最後に置き、前の 4 本がマージされてから着手する。

## Goal

ジャケット由来のコードと画像ファイルをリポジトリとストレージから消し、参照が 1 つも残らない状態にする。

## Scope

- `src/viewer/src/components/viewer/JacketArt.astro`
- `src/viewer/src/shared/image.ts` の `JACKET_WIDTHS` とジャケット向けの記述
- `src/viewer/src/styles/viewer.css` の `.jacket-art` 系
- ストレージの `release-jacket-art/` 配下

## Non-Scope

- 色の抽出機能（将来別途）
- YouTube サムネイル（`media/[mediaId].astro` の OGP は変更しない）
- DB カラム（1 本目で置き換え済み）

## Acceptance Criteria

- リポジトリ全体を `jacket` で検索して、生成コードを含め 1 件もヒットしない
- ストレージの `release-jacket-art/` が空になっている
- viewer / admin / server の静的検査とテストが通る
- viewer の全ページが表示でき、画像は YouTube サムネイルだけになっている

## Steps

- [ ] `JacketArt.astro` を削除する
- [ ] `shared/image.ts` から `JACKET_WIDTHS` を削除し、残る利用者（YouTube サムネイル）に合わせて記述を整理する
- [ ] `viewer.css` から `.jacket-art` 系のスタイルを削除する
- [ ] ストレージの `release-jacket-art/` 配下を削除する
- [ ] リポジトリ全体を `jacket` で検索し、残りが無いことを確認する

## Decision Log

共通の判断は `20260809-release-color-column.md` を参照。

- 2026-08-09: 不可逆な削除（ストレージ）はこの 1 本に隔離し、5 本の最後に置く
- 2026-08-09: ストレージの退避は行わない。本番のファイルは削除済みで、ローカルの MinIO に残っている 87 件は今回使わない

## Validation

-
