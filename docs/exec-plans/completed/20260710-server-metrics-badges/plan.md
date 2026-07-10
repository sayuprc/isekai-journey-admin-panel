# Execution Plan

## Title

Server テストメトリクスの定期可視化（カバレッジ / Infection / PhpMetrics）

## Status

completed

## Background

`src/server` にはすでに `composer coverage` / `infection` / `metrics` と対応する `mise` タスクがあるが、結果はローカルの `reports/` にしか出ず、CI では PHPUnit 実行のみ。定期的に数値を見返したり README で一目できるようにしたい。

## Goal

定期 CI で coverage / mutation / code metrics を計測し、shields.io 向け JSON を `metrics` ブランチへ公開して README バッジから確認できるようにする。詳細レポートは Actions Artifact としても残す。

## Scope

- `.github/workflows/server-metrics.yaml`（schedule + workflow_dispatch）
- `src/server/phpmetrics.json` の JSON 出力追加
- バッジ / サマリ生成スクリプト（`tools/metrics/`）
- `mise` / composer の CI 向け出力パス調整（必要なら）
- `README.md` のバッジ表示
- `docs/metrics/` の説明とバッジ JSON のプレースホルダ

## Non-Scope

- PR ごとの必須ゲート化や MSI / coverage 閾値での fail
- Codecov / Coveralls / Sonar 等の外部 SaaS 導入
- Gist PAT など追加シークレット依存
- Admin / Viewer / Contracts のメトリクス

## Acceptance Criteria

- `workflow_dispatch` / 定期 schedule で server の coverage・infection・phpmetrics が走る
- shields.io endpoint 形式の JSON が `metrics` ブランチの `docs/metrics/badges/` に更新される
- README からカバレッジ / MSI / 主要コードメトリクスのバッジが見える
- HTML 等の詳細レポートが Actions Artifact として残る
- 追加のリポジトリシークレットなしで動く

## Steps

- [x] 計測結果 → shields endpoint JSON / summary JSON を生成するスクリプトを追加する
- [x] `phpmetrics.json` に JSON レポート出力を追加する
- [x] coverage / infection が機械可読出力を出すよう composer / mise / infection 設定を調整する
- [x] `.github/workflows/server-metrics.yaml` を追加し、成功時に `metrics` ブランチへバッジを push、Artifact を残す
- [x] `README.md` と `docs/metrics/README.md` を更新する
- [x] ローカルでスクリプトをサンプル入力で検証する

## Decision Log

- 2026-07-10: 外部 SaaS / Gist PAT は使わず、専用 `metrics` ブランチ + `raw.githubusercontent.com` + shields.io endpoint でバッジ化する。既存の `check-index.yml` と同様に `GITHUB_TOKEN` だけで完結し、メトリクス更新で `dev` にノイズ PR を出さないため。
- 2026-07-10: 実行は schedule（週次）+ `workflow_dispatch`。Infection が重いため PR / 毎 push の Server QA には載せない。
- 2026-07-10: バッジ対象は coverage %、Infection MSI %、PhpMetrics の平均 Maintainability Index。詳細は Artifact / summary JSON。

## Validation

- `python3 -m unittest tools.metrics.test_build_server_badges -v` が通る
- `.github/workflows/server-metrics.yaml` が YAML としてパースできる
- README の shields.io endpoint URL が `metrics` ブランチの `docs/metrics/badges/*.json` を指す

