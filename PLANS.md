# PLANS.md

## 位置づけ

実行計画は第一級の成果物です。小さい作業でも、複雑さがあるなら短い計画を書いてから進めます。

## いつ計画を書くか

- `src/contracts`、`src/server`、`src/admin`、`src/viewer` の 2 つ以上にまたがる変更
- UI と API、または UI と業務ロジックの両方を触る変更
- スタック選定や構造変更を含む変更
- 複数回の確認やレビューが必要そうな変更

## 置き場所

- 進行中: `docs/exec-plans/active/`
- 完了後: `docs/exec-plans/completed/`

## 計画に必ず入れるもの

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
- 完了したら `completed/` に移す
- 実装結果と食い違う計画は放置しない

テンプレートは `docs/exec-plans/template.md` を使います。
