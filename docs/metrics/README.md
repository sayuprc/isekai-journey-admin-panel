# Server Metrics

`src/server` のテストカバレッジ・ミューテーションテスト・コードメトリクスを定期計測し、README バッジから見返せるようにする仕組みです。

## 見えるもの

| バッジ | 意味 | 元データ |
| --- | --- | --- |
| coverage | PHPUnit 行カバレッジ % | `reports/coverage.xml` (Clover) |
| mutation score | Infection MSI % | `reports/infection-summary.json` |
| covered MSI | カバー済みコードの MSI % | 同上 |
| maintainability | PhpMetrics 平均 Maintainability Index | `reports/metrics.json` |
| avg CCN | 平均サイクロマティック複雑度 | `reports/metrics-summary.json` |

詳細サマリは `metrics` ブランチの `docs/metrics/summary.json` にあります。HTML レポートなどは GitHub Actions の Artifact `server-metrics` に残ります。

## 仕組み

1. 週次 / 手動 CI で coverage・Infection・PhpMetrics を計測する
2. `tools/metrics/build-server-badges.sh`（bash + jq）がレポートから shields.io endpoint JSON を作る
3. `tools/metrics/publish-metrics-branch.sh` が `metrics` ブランチへ push する
4. README のバッジがその JSON を読む

Python は使いません。追加シークレットも不要です。

## 実行タイミング

- 週次 schedule（月曜 00:00 UTC）
- `workflow_dispatch`（手動）

PR / 毎 push の `Server QA` には載せません（Infection が重いため）。

## ローカルで同じ出力を作る

```bash
mise run api:coverage
mise run api:infection
mise run api:metrics
mise run api:metrics:badges
```

または直接:

```bash
./tools/metrics/build-server-badges.sh \
  --clover src/server/reports/coverage.xml \
  --infection-summary src/server/reports/infection-summary.json \
  --phpmetrics src/server/reports/metrics.json \
  --phpmetrics-summary src/server/reports/metrics-summary.json \
  --output-dir artifacts/server-metrics \
  --git-sha "$(git rev-parse HEAD)"
```

フィクスチャでのスクリプト検証:

```bash
./tools/metrics/test-build-server-badges.sh
```

## バッジ URL

`metrics` ブランチの raw JSON を shields.io endpoint で描画します。

```text
https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/sayuprc/isekai-observatory/metrics/docs/metrics/badges/<name>.json
```
