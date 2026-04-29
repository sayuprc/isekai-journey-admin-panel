---
name: orchestrator
description: >
  サブエージェントを活用した実装タスクのオーケストレーター。新規実装・バグ修正・リファクタリングを「問題定義 → 実装計画 → 実装 → レビュー」の 4 フェーズで進める。ユーザーが「orchestrator で」「エージェントを使って実装して」「サブエージェントに任せて」「/orchestrator」と明示した場合、または複数ステップにわたる複雑な実装タスクで段階的なアプローチを求められたときに使う。
---

# オーケストレーター

サブエージェントを使ってタスクを段階的に進めるスキル。各フェーズでユーザーの承認を得てから次に進む。

## ワークフロー

```
Issue（問題定義）→ Plan（実装計画）→ Implementation（実装）→ Review（レビュー）
       ↑ ユーザー承認まで繰り返し    ↑ ユーザー承認まで繰り返し         ↑ ユーザー指示で修正ループ
```

## 作業ファイル

- 配置先: `docs/exec-plans/active/YYYYMMDD-<slug>.md`
- フォーマット: `docs/exec-plans/template.md` に準拠
- 完了後: `docs/exec-plans/completed/` へ移動

Issue フェーズで作成し、Plan フェーズで追記する。1 タスク 1 ファイル。

## フェーズ 1: Issue（問題定義）

`agents/issue-agent.md` の指示を読み、その内容を prompt に含めてサブエージェントを起動する。

サブエージェントへの情報:
- ユーザーのタスク説明
- 作業ファイルのパス（`docs/exec-plans/active/YYYYMMDD-<slug>.md`）
- exec-plans テンプレートの場所（`docs/exec-plans/template.md`）

サブエージェント完了後、ファイルをユーザーに表示して「このissue定義でよいですか？」と確認する。フィードバックがあれば改善サブエージェントを再起動し、「良い」「問題ない」「承認」など肯定の返答があればフェーズ 2 へ進む。

## フェーズ 2: Plan（実装計画）

`agents/plan-agent.md` の指示を読み、その内容を prompt に含めてサブエージェントを起動する。

サブエージェントへの情報:
- 作業ファイルのパス（Issue フェーズで作成済み）
- コードベースへのアクセス権限

サブエージェントは作業ファイルに Steps, Decision Log, Validation を追記し、Status を `planned` に更新する。

サブエージェント完了後、Steps セクションをユーザーに提示して「この実装計画でよいですか？」と確認する。承認まで改善を繰り返す。

## フェーズ 3: Implementation（実装）

作業ファイルの Status を `in-progress` に更新してから、`agents/impl-agent.md` の指示を読みサブエージェントを起動する。

サブエージェントへの情報:
- 作業ファイルのパス（Plan フェーズで完成済み）
- 実装対象のコードベース

サブエージェントは計画の Steps を順番に実行し、完了したステップに ✅ を付ける。

## フェーズ 4: Review（レビュー）

`agents/review-agent.md` の指示を読みサブエージェントを起動する。

サブエージェントへの情報:
- 作業ファイルのパス
- 実装されたファイルの一覧（git diff などで特定）

サブエージェント完了後、レビュー結果をユーザーに提示し「どの指摘を修正しますか？（なければ完了）」と尋ねる。

- **修正指示あり**: Implementation サブエージェントを再起動 → Review に戻る
- **修正なし/完了**: Status を `completed` に更新し、`docs/exec-plans/completed/` へファイルを移動して終了

## エージェント指示ファイル

各フェーズのサブエージェントの詳細指示:

- `agents/issue-agent.md` — 問題定義エージェント
- `agents/plan-agent.md` — 実装計画エージェント
- `agents/impl-agent.md` — 実装エージェント
- `agents/review-agent.md` — レビューエージェント

サブエージェントを起動する前に必ず該当ファイルを読み、その内容を prompt に組み込む。
