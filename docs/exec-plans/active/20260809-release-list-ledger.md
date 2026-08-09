# Title

リリース一覧を年表の台帳に作り替える

## Status

planned

## Background

ジャケットアート廃止にともない、`src/viewer/src/pages/releases/index.astro` のカードグリッドは主役の画像を失う。画像を抜いた 3 カラムの正方形グリッドを維持する理由がないため、年でグルーピングした台帳へ全面的に作り替える。

`discography-mock.html` がこの案のモックで、実データ 84 リリースグループ（2020〜2026 の 7 年）で見た目を確認済み。

前提となる `releases.color` は `20260809-release-color-column.md` で追加する。

## Goal

`/releases` を年ごとの台帳に置き換え、色帯と色見本でリリースを識別できるようにする。現行の検索・フィルタ機能は落とさない。

## Scope

- `src/viewer/src/pages/releases/index.astro` を年グルーピングの一覧に書き換える
- `src/viewer/src/features/releases/types.ts` にグループ代表色の導出を追加する
- `src/viewer/src/styles/viewer.css` の `.release-grid` / `.release-card` 系を台帳のスタイルに差し替える

## Non-Scope

- ホーム・リリース詳細・楽曲タイル（3〜4 本目で行う）
- `JacketArt.astro` の削除（5 本目で行う）
- 収録曲数や提供形態の表示（一覧には出さない）

## Acceptance Criteria

- `/releases` が年の降順に並び、各年の見出しに件数が出る
- 行は 色の縦棒 / 色見本 / タイトル / 種別 / 日付 の 5 要素で構成される
- タイトル・種別・収録曲名での検索と種別フィルタが従来どおり動く
- 検索・フィルタの結果が 0 件になった年のセクションが畳まれ、グルーピングの軸は変わらない
- 長いタイトル（`その他` 19 件のライブ映像作品）が PC でも 2 行まで折り返して読める
- 明 3 / 暗 3 のどのパレットでも色見本と地のコントラストが破綻しない

## Steps

- [ ] `src/viewer/src/features/releases/types.ts` に `representativeColor()` を追加する。`representativeJacketArtUrl` と同じく、公開リリースを発売日順に見て最初のものを採る
- [ ] `index.astro` を年グルーピングに書き換える。`firstReleasedOn` の年で束ね、年内は API の返却順（新しい順）を維持する
- [ ] 行のマークアップを 色の縦棒 / 色見本 / タイトル / 種別 / 日付 に置き換え、`data-release-entry` などフィルタ用の属性は現行のまま残す
- [ ] `FilterBar` と `EntryStatus` の配線を維持したうえで、`EntryStatus` が年セクションの畳み込みも行えるようにする
- [ ] `viewer.css` の `.release-grid` / `.release-card` 系を削除し、台帳のスタイルを追加する。タイトルは 2 行までの折り返しにする
- [ ] 6 パレットすべてで色見本のコントラストを目視確認し、必要なら表示時に明度を丸める処理を入れる

## Decision Log

共通の判断は `20260809-release-color-column.md` を参照。

- 2026-08-09: カードグリッドをやめて年表の台帳にする。画像が無いなら情報密度で見せるほうが強く、`discography-mock.html` で構造を確認済み
- 2026-08-09: 収録曲数・枚組・提供形態（配信 / CD / Blu-ray）は一覧に出さない。行の情報量を絞る
- 2026-08-09: タイトルは PC でも 2 行まで折り返す。切れる行が全体の 2 割あり例外ではないため
- 2026-08-09: 同名・同日のリリースグループ（`再会` のシングルと EP など）への追加対応はしない。種別列で区別できる
- 2026-08-09: 検索中も年グルーピングを維持し、0 件の年は畳む。検索の有無でレイアウトの軸が変わると結果の並び順が読めなくなるため

## Validation

-
