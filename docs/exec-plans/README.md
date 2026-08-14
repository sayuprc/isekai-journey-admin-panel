# Exec Plans

ここには、会話だけでは追いにくい複数ステップの作業計画を置きます
上位の入口は `PLANS.md` を参照する

## 使い分け

- `active/`: 進行中の計画
- `completed/`: 完了した計画
- `template.md`: 計画の雛形

## ファイル構成

1 タスクごとに 1 ファイルを作ります

```
active/
  YYYYMMDD-<slug>.md   # 問題定義と実装計画(template.md 参照)
```

完了したら `completed/` に移します

## どんな時に作るか

- `src/contracts`、`src/server`、`src/admin`、`src/viewer` の 2 つ以上にまたがる変更
- UI と API、または UI と業務ロジックの両方を触る変更
- スタック選定や構造変更を含む変更
- 手順の順番が重要な変更
- 判断ログや検証記録を残したい変更
- 複数回の確認やレビューが必要そうな変更

## 計画に必ず入れるもの

- `Title`
- `Status`
- `Background`
- `Goal`
- `Scope`
- `Non-Scope`
- `Acceptance Criteria`
- `Steps`
- `Decision Log`
- `Validation`

## 運用ルール

- 長文にしない。実行に必要な情報だけを書く
- 作業中に前提が変わったら更新する
- 完了したら `completed/` へ移す
- 実装結果と食い違う計画は放置しない

小さな変更は軽量な TODO で十分ですが、数回のセッションにまたがる作業はここに残します
テンプレートは `docs/exec-plans/template.md` を使います
