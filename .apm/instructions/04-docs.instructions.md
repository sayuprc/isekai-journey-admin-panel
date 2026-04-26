---
name: 'Documentation Instructions'
description: 'Use when updating README, ARCHITECTURE.md, FRONTEND.md, PLANS.md, ADRs, product specs, references, exec plans, or other Markdown docs. Covers repository-as-record-system conventions and document placement.'
applyTo: '**/*.md'
---

# 文書規約

- 長い手引きより短い地図を優先する。入口文書は要約に徹し、詳細は別文書へリンクする
- 安定した判断は ADR、継続的な設計原則は `docs/design-docs/`、機能や施策の仕様は `docs/product-specs/`、外部資料の要約は `docs/references/`、進行中の複雑な作業は `docs/exec-plans/`、負債は `docs/tech-debt-tracker.md` に置く
- 会話やレビューで繰り返し出るルールは、次回以降も使える形でリポジトリに残す
- 文書には事実、ディレクトリ名、コマンド、責務境界を書く。抽象論だけで終わらせない
- コード変更が文書を古くしたら、同じ変更で近接する文書も更新する
