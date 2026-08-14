# Title

管理画面で画像からリリース代表色をブラウザ抽出する

## Status

completed

## Background

`releases.color` は手入力のみで、移行時の固定値 `#989899` のまま実データ投入が難しい
当初は BFF で Node + node-vibrant を想定していたが、画像をサーバーへ送ると一時保管・ログ・転送の論点が増える
ブラウザ内で抽出し、選んだ hex だけを既存の create/update で保存する方が ADR-0015(画像を保存・配信しない)に沿いやすい

## Goal

リリース作成・詳細の代表色入力に、ローカル画像からの色抽出 UI を追加する
画像は端末外に送らず、選択した `#rrggbb` だけを既存 API で永続化する

## Scope

- `src/admin` への `node-vibrant` 追加
- 抽出ユーティリティと共有 `ColorField` UI
- `CreateForm` / `DetailView` への配線
- Decision Log(本計画と `20260809-release-color-column.md`)の更新

## Non-Scope

- BFF / Laravel への画像アップロード API
- モザイク・サムネイル・縮小画像の返却やプレビュー表示
- 既存リリースへの一括色入れ直し
- viewer 側の表示変更

## Acceptance Criteria

- 管理画面のリリース作成・詳細で画像を選ぶと、候補色が表示され、彩度×面積スコア最大が初期選択になる
- 候補・カラーピッカー・hex テキストのいずれでも同じ `color` 値を更新できる
- 抽出に失敗しても手入力で保存を続行できる
- ネットワーク上に画像バイナリが送られない(保存リクエストの body は hex のみ)
- `node-vibrant/browser` 経由のクライアント抽出であり、BFF に画像処理が無い

## Steps

- [x] `docs/exec-plans/active/20260809-release-color-column.md` の Decision Log をブラウザ抽出に更新する
- [x] `src/admin` に `node-vibrant` を追加する
- [x] `src/admin/src/utils/extractColors.ts` を追加する(object URL → palette → 正規化 hex)
- [x] `src/admin/src/components/release/ColorField.tsx` を追加し、Create/Detail の代表色 UI を置き換える
- [x] `lint:check` / 型検査で問題がないことを確認する

## Decision Log

- 2026-08-09: 色抽出は BFF/Laravel ではなくブラウザ(`node-vibrant/browser`)で行う
  画像をサーバーに送らないことで ADR-0015 の「受け取った画像は保存も配信もしない」を構造的に満たしやすいため
- 2026-08-09: デフォルト候補は Vibrant。Muted / DarkVibrant / DarkMuted / LightVibrant / LightMuted も選択可能にする
- 2026-08-09: ジャケット画像のプレビューは出さない。候補は単色スウォッチのみとし、モザイクやサムネイルを出さない
- 2026-08-09: 永続化は既存の release create/update の `color` のみ。抽出用の API は追加しない
- 2026-08-09: 初期選択と候補順は Vibrant 固定ではなく、彩度×面積(population)スコア最大を優先する
  くすんだ大面積より、ある程度彩度のある色を残したいため。彩度 0.22 未満は候補プールから外し、全滅時だけフォールバック
- 2026-08-09: admin の ColorField にベタ / グラデ / ノイズの見え方プレビューを並べる
  viewer への採用はプレビュー比較後に決める。保存値は引き続き hex 1 色のみ
- 2026-08-09: 見え方比較ではノイズ単体が好まれたが、ホーム最新リリースは案1(大きな色面なし)を採用したため、
  viewer / admin ともノイズ表現は載せない。カラーピッカーと hex・候補スウォッチだけで足りる

## Validation

- `bun run lint:check`(admin): OK
- `bunx tsc --noEmit`(admin): 新規ファイルにエラーなし(既存の傘下ブランチ由来エラーは残存)
- `bun run build`(admin): OK
- #1001 として `feature/remove-jacket-art` にマージ済み
