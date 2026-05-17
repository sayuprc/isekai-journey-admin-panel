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

1 タスクにつき 1 ディレクトリを作り、問題定義と実装計画を別ファイルに分ける。

- 配置先: `docs/exec-plans/active/YYYYMMDD-<slug>/`
  - `issue.md`: Issue フェーズで作成（問題定義）
  - `plan.md`: Plan フェーズで作成（実装計画）
- フォーマット:
  - `issue.md` は `docs/exec-plans/template-issue.md` に準拠
  - `plan.md` は `docs/exec-plans/template-plan.md` に準拠
- 完了後: ディレクトリごと `docs/exec-plans/completed/YYYYMMDD-<slug>/` へ移動

## フェーズ 1: Issue（問題定義）

`agents/issue-agent.md` の指示を読み、その内容を prompt に含めてサブエージェントを起動する。

サブエージェントへの情報:
- ユーザーのタスク説明
- 作業ディレクトリのパス（`docs/exec-plans/active/YYYYMMDD-<slug>/`）
- 出力ファイルのパス（`docs/exec-plans/active/YYYYMMDD-<slug>/issue.md`）
- テンプレートの場所（`docs/exec-plans/template-issue.md`）

サブエージェント完了後、`issue.md` をユーザーに表示して「この issue 定義でよいですか？」と確認する。フィードバックがあれば改善サブエージェントを再起動し、「良い」「問題ない」「承認」など肯定の返答があればフェーズ 2 へ進む。

## フェーズ 2: Plan（実装計画）

`agents/plan-agent.md` の指示を読み、その内容を prompt に含めてサブエージェントを起動する。

サブエージェントへの情報:
- 入力ファイルのパス（`docs/exec-plans/active/YYYYMMDD-<slug>/issue.md`）
- 出力ファイルのパス（`docs/exec-plans/active/YYYYMMDD-<slug>/plan.md`）
- テンプレートの場所（`docs/exec-plans/template-plan.md`）
- コードベースへのアクセス権限

サブエージェントは `issue.md` を読んだうえで `plan.md` を新規作成し、Steps, Decision Log, Validation を書き、Status を `planned` に設定する。`issue.md` には触らない。

サブエージェント完了後、`plan.md` の Steps セクションをユーザーに提示して「この実装計画でよいですか？」と確認する。承認まで改善を繰り返す。

## フェーズ 3: Implementation（実装）

`plan.md` の Status を `in-progress` に更新してから、`agents/impl-agent.md` の指示を読みサブエージェントを起動する。

サブエージェントへの情報:
- 作業ディレクトリのパス（`docs/exec-plans/active/YYYYMMDD-<slug>/`）
- 主に参照するファイル: `plan.md`（Steps と Decision Log）
- 文脈参照用: `issue.md`（Acceptance Criteria など）
- 実装対象のコードベース

サブエージェントは `plan.md` の Steps を順番に実行し、完了したステップに ✅ を付ける。

## フェーズ 4: Review（レビュー）

`agents/review-agent.md` の指示を読みサブエージェントを起動する。

サブエージェントへの情報:
- 作業ディレクトリのパス（`docs/exec-plans/active/YYYYMMDD-<slug>/`）
  - `issue.md`: Acceptance Criteria・Scope の参照元
  - `plan.md`: Steps の達成状況の参照元
- 実装されたファイルの一覧（git diff などで特定）

サブエージェント完了後、レビュー結果をユーザーに提示し「どの指摘を修正しますか？（なければ完了）」と尋ねる。

- **修正指示あり**: Implementation サブエージェントを再起動 → Review に戻る
- **修正なし/完了**: `plan.md` の Status を `completed` に更新し、ディレクトリごと `docs/exec-plans/completed/YYYYMMDD-<slug>/` へ移動して終了

## エージェント指示ファイル

各フェーズのサブエージェントの詳細指示:

- `agents/issue-agent.md` — 問題定義エージェント
- `agents/plan-agent.md` — 実装計画エージェント
- `agents/impl-agent.md` — 実装エージェント
- `agents/review-agent.md` — レビューエージェント

サブエージェントを起動する前に必ず該当ファイルを読み、その内容を prompt に組み込む。
