# viewer パフォーマンス計測

viewer の改善効果を本番環境で計測し、記事用のデータセットを残すための記録場所です
計測の方針と施策の分類は `docs/exec-plans/active/20260805-viewer-perf-measurement.md` を参照してください

## 2 つのツールの使い分け

| | `perf:scan` (unlighthouse) | `perf:measure` (lighthouse) |
| --- | --- | --- |
| 目的 | **探索**: 全ページから遅いページを見つける | **計測**: 施策の前後比較 |
| 対象 | サイト全ページ (約 1300) | `pages.json` の固定 8 ページ |
| 実行 | 並列 4、1 ページ 1 回 | 直列、1 ページ 9 回 |
| 出力 | 順位 | 中央値と IQR |
| 頻度 | baseline と最終形の 2 回 | 施策ごと |

**`perf:scan` の数値を記事のグラフに使わないでください**
並列クロールで値が下振れし、実際に `/releases/` で 8.2s (measure) に対し 17.2s (scan) という差が出ています
scan は「どのページを `pages.json` に載せるか」を決めるためだけに使い、比較する値は measure で取り直します

また scan は unlighthouse が自前で用意する Chrome を使い、measure の puppeteer とはバージョンが異なります
この点でも両者の絶対値は比較できません

## 実行

```bash
mise run perf:install          # 初回のみ

mise run perf:scan baseline    # 遅いページを探す
mise run perf:measure baseline # 固定ページを計測する
```

直接実行する場合は `tools/perf` で以下を叩きます

```bash
node measure.ts --label baseline
node measure.ts --label 921-logo-svg --form-factor mobile
node measure.ts --label 923-self-host-fonts --pages songs-index,contact --runs 5
```

| オプション | 既定 | 説明 |
| --- | --- | --- |
| `--label` | (必須) | 計測ラベル。施策を識別する名前を付ける |
| `--runs` | 9 | 1 ページあたりの計測回数。中央値と IQR を出すため奇数にする |
| `--warmup` | 3 | 本計測の前に捨てるリクエスト数 |
| `--form-factor` | both | `mobile` / `desktop` / `both` |
| `--pages` | 全件 | 計測するページキーをカンマ区切りで指定する |
| `--config` | `pages.json` | 計測対象の定義ファイル |

## 成果物

| パス | 内容 | git |
| --- | --- | --- |
| `results.jsonl` | 1 計測 1 行のサマリ (中央値と IQR、転送量)。記事のグラフはこれを入力にする | 追跡する |
| `runs.jsonl` | run 1 行の指標と LCP 要素。分布を見るときはこちら | 追跡する |
| `reports/<label>/<formFactor>/<page>/run-N.json` | 各 run の Lighthouse 生レポート | 追跡しない |
| `reports/<label>/<formFactor>/<page>/median.html` | 中央値の run の HTML レポート | 追跡しない |
| `scans/<label>/ci-result.json` | 全ページスキャンの結果 | 追跡しない |

`reports/` と `scans/` は合計 3.3GB になるため git 管理から外しています
消すと生レポートは再現できないので、記事を書き終えるまでローカルに残してください

ただし run ごとの指標は `runs.jsonl` に抽出済みです
中央値と IQR だけでは run 1 のコールドや分布の二峰性が追えないため、生レポートを消す前に必ず実行してください

```bash
node extract-runs.ts
```

## 遅いページを探す

```bash
mise run perf:scan baseline
```

mobile と desktop を順に走査し、ルート種別ごとに LCP の遅い順で上位 5 件を表示します
結果を見て `pages.json` の詳細ページを差し替えます

設定は `tools/perf/unlighthouse.config.ts` にあります

- `dynamicSampling: false` — 既定では `/songs/[id]` のような動的ルートが 5 件に間引かれる
- `maxRoutes: 2000` — 既定の上限 200 を超えた分は黙って捨てられる
- ID が壊れているページ (UUID に `efbfbd` = U+FFFD が混入) は順位から除外する

過去のスキャン結果を集計し直したい場合は `rank.ts` を直接叩きます

```bash
node rank.ts ../../docs/perf/scans/baseline/{mobile,desktop}/ci-result.json
```

### HTML レポートを見る

`--build-static` 付きで実行しているため、各 form factor のディレクトリがそのまま静的サイトになっています
`file://` で開くと `payload.js` の読み込みに失敗するので HTTP で配信してください

```bash
pnpm dlx serve docs/perf/scans/baseline/mobile
```

## レポートを後から見返す

- `median.html` をブラウザで開くと通常の Lighthouse 画面がそのまま表示されます
- `run-N.json` は [Lighthouse Viewer](https://googlechrome.github.io/lighthouse/viewer/) にドラッグ&ドロップすると同じ UI で開けます。JS バンドルの内訳を見る Treemap もここから辿れます

## 計測条件

`measure.ts` の `PRESETS` にスロットリング設定を直接書いてあります
Lighthouse の既定プリセットと同値ですが、既定値の変更に引きずられないよう固定しています

| | mobile | desktop |
| --- | --- | --- |
| RTT | 150ms | 40ms |
| 帯域 | 1638.4kbps | 10240kbps |
| CPU | 4x slowdown | 1x |
| 画面 | 412x823 (dsf 1.75) | 1350x940 (dsf 1) |

## 記録する指標

- **ラボ指標**: LCP / FCP / CLS / TBT / Speed Index / TTFB / Performance スコア。それぞれ中央値と IQR
- **転送量**: resourceType 別に `transfer` (圧縮後) と `resource` (圧縮前) の両方
  - 本番は zstd が効くため、issue に書かれた生サイズの削減率と圧縮後の削減率は一致しません。記事では両方を出します
- **クリティカルチェーンの深さ**: `network-dependency-tree-insight` から算出。#923 の効果がここに出ます
- **コンテンツ件数**: 楽曲 / リリース / メディアの数を sitemap から取得
  - SSG なので計測期間中にデータが増えると転送量が動きます。施策の効果と混ざっていないかをこの値で確認します
- **キャッシュ状態**: `cf-cache-status` / `age` / `content-encoding`

## 注意

- 計測対象ページの ID は `pages.json` に固定しています。計測期間中は変更しないでください
- 計測期間中は viewer のコンテンツを追加しないでください。追加した場合は `counts` の変化点を記事で断ってください
