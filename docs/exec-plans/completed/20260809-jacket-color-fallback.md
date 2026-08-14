# Title

リリース詳細と楽曲タイルのジャケット利用を色に置き換える

## Status

completed

## Background

1 本目でジャケットの表示は既に落ちている。この計画では、色による表現をまだ入れていない 2 箇所を仕上げる

- `src/viewer/src/components/viewer/details/ReleaseVersionBody.astro`: `has-jacket` レイアウトの残骸を外し、版ごとの色帯を入れる
- `src/viewer/src/components/viewer/SongTile.astro`: 1 本目でアイコンにフォールバックしている楽曲へ色を敷く

`SongTile` のフォールバックは実際に効いている。公開中 371 曲のうち **60 曲がメディア未登録**で、
1 本目の時点ではこの 60 曲が無地のアイコンになっている

## Goal

リリース詳細の版セクションと楽曲タイルに代表色を入れ、1 本目で失われた識別子を取り戻す

## Scope

- `src/viewer/src/components/viewer/details/ReleaseVersionBody.astro`
- `src/viewer/src/components/viewer/SongTile.astro`
- `src/viewer/src/components/viewer/details/SongDetailContent.astro` の収録リリース表示
- `src/viewer/src/styles/viewer.css` の対応するスタイル

## Non-Scope

- `JacketArt.astro` そのものの削除(5 本目で行う)
- YouTube サムネイルの扱い(311 曲はそのまま残す)

## Acceptance Criteria

- リリース詳細の版セクションに版ごとの色帯が出て、`has-jacket` の分岐が無くなっている
- メディア未登録の 60 曲のタイルに収録リリースの代表色が敷かれ、無地のアイコンが出ない
- YouTube サムネイルがある 311 曲の表示は変わらない
- 楽曲ドロワーの収録リリース一覧から画像が無くなっている

## Steps

- [x] `ReleaseVersionBody.astro` に版ごとの色帯を入れ、`has-jacket` の分岐とスタイルを削除する
- [x] `SongTile.astro` のアイコンフォールバックに収録リリースの色を敷く
      色の採り方は従来のジャケットと同じく `releaseGroups` の先頭から最初に見つかったものを使う
- [x] `SongDetailContent.astro` の収録リリースに色見本を入れる
- [x] `viewer.css` の `.jacket-art` 依存スタイルのうち、この 2 箇所に紐づくものを整理する
- [x] メディア未登録の楽曲が一覧でどう見えるか確認する

## Decision Log

共通の判断は `20260809-release-color-column.md` を参照

- 2026-08-09: メディア未登録の楽曲は収録リリースの色を地に敷く。無地のアイコンを 60 枚出すのは避けたく、YouTube サムネイル 311 枚を捨てるのは廃止の理由と釣り合わないため
- 2026-08-09: 楽曲タイルの色は `releaseGroups` の先頭から最初に見つかったものを採る。既存の導出規則をそのまま流用でき、新しい並べ替えを足さずに済む
- 2026-08-09: リリース詳細の版セクションには版ごとの色帯を出す。効くのは版が複数ある 4 グループだけだが、色を版に持つ判断と整合する
- 2026-08-09: 収録リリースが無いカバー曲などは色を敷けないので、従来のアイコンフォールバックのまま残す

## Validation

- `mise run viewer:check` 通過
- `bun --filter viewer build` 通過。楽曲一覧 371 曲のうち YouTube サムネ 309・色フォールバック 23・収録リリース無しのアイコン 39
  `has-jacket` / `release-version-art` は出力から消えている
