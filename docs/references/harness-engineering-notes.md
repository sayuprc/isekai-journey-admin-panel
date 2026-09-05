# Harness Engineering Notes

OpenAI の Harness Engineering 記事をこのリポジトリ向けに要約したメモです

## このリポジトリに持ち込む要点

- 入口文書は短い地図にして、詳細知識は別文書へ分ける
- エージェントが読めない情報は、存在しないのと同じとみなす
- 複雑な変更は実行計画としてリポジトリ内に残す
- 仕様が曖昧な変更は、先に spec を書いてから着手する
- 繰り返し出るレビューは、会話ではなく文書や設定へ昇格させる

## このリポジトリ向けの補足

- `docs/agent-map.md` を共通の短い入口として置き、共通知識はそこから下位文書へ辿れる形にする
- この repo では `docs/` を内部向けの記録システムとして使っている
- API の Source of Truth は `src/contracts` の TypeSpec であり、文書はその変更フローを補助する

## いまはまだやらないこと

- 巨大な `docs/agent-map.md` を育てること
- `AGENTS.md` に共通ルールや tool-specific な詳細ルールまで複写して二重管理すること
- まだ困っていない段階で文書の置き場をさらに増やすこと
- ドキュメントだけで解決できる問題に、先回りして専用ツールを増やすこと

## 既にあるもの

- `docs/product-specs/` の feature 用仕様 (現状は少数)
- `docs/exec-plans/completed/` の完了計画履歴
- `docs/operations/` の運用手順

## 次に追加しやすいもの

- 追加の feature 用 `docs/product-specs/` 文書
- 進行中作業向けの `docs/exec-plans/active/` 計画
- 管理画面や閲覧サイトの個別 UI ガイド
- ドメイン別の design doc
