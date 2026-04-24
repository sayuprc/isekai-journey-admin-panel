# Harness Engineering Notes

OpenAI の Harness Engineering 記事をこのリポジトリ向けに要約したメモです。

## このリポジトリに持ち込む要点

- 入口文書は短い地図にして、詳細知識は別文書へ分ける
- エージェントが読めない情報は、存在しないのと同じとみなす
- 複雑な変更は実行計画としてリポジトリ内に残す
- 仕様が曖昧な変更は、先に spec を書いてから着手する
- 繰り返し出るレビューは、会話ではなく文書や設定へ昇格させる

## このリポジトリ向けの補足

- ワークスペース全体の入口は `AGENTS.md` ではなく `.github/copilot-instructions.md` を正本として維持する
- `CLAUDE.md` は `.github/copilot-instructions.md` へのシンボリックリンクとして使う
- この repo では `docs/` を内部向けの記録システムとして使っている
- API の Source of Truth は `src/contracts` の TypeSpec であり、文書はその変更フローを補助する

## いまはまだやらないこと

- 巨大な `AGENTS.md` を追加すること
- `AGENTS.md` と `.github/copilot-instructions.md` の二重管理
- まだ困っていない段階で文書の置き場をさらに増やすこと
- ドキュメントだけで解決できる問題に、先回りして専用ツールを増やすこと

## 次に追加しやすいもの

- 実際の feature 用 `docs/product-specs/` 文書
- 具体的な変更を追う `docs/exec-plans/active/` の計画
- 管理画面や閲覧サイトの個別 UI ガイド
- ドメイン別の design doc
