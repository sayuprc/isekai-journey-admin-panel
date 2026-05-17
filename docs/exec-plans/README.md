# Exec Plans

ここには、会話だけでは追いにくい複数ステップの作業計画を置きます。

## 使い分け

- `active/`: 進行中の計画
- `completed/`: 完了した計画
- `template-issue.md`: 問題定義の雛形
- `template-plan.md`: 実装計画の雛形

## ファイル構成

1 タスクごとにディレクトリを作り、問題定義と実装計画を別ファイルに分けます。

```
active/
  YYYYMMDD-<slug>/
    issue.md   # 問題定義（Background, Goal, Scope, Non-Scope, Acceptance Criteria）
    plan.md    # 実装計画（Status, Steps, Decision Log, Validation）
```

完了したら `YYYYMMDD-<slug>/` ディレクトリごと `completed/` に移します。

## どんな時に作るか

- 複数パッケージにまたがる変更
- 手順の順番が重要な変更
- 判断ログや検証記録を残したい変更

## 計画に必ず入れるもの

`issue.md`:

- `Title`
- `Background`
- `Goal`
- `Scope`
- `Non-Scope`
- `Acceptance Criteria`

`plan.md`:

- `Title`
- `Status`
- `Steps`
- `Decision Log`
- `Validation`

## 運用ルール

- 長文にしない。実行に必要な情報だけを書く
- 作業中に前提が変わったら更新する
- 完了したらディレクトリごと `completed/` へ移す
- 実装結果と食い違う計画は放置しない

小さな変更は軽量な TODO で十分ですが、数回のセッションにまたがる作業はここに残します。
