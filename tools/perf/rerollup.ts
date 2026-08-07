#!/usr/bin/env node
// 保存済みの run レポートから results.jsonl を再集計する
//
// 計測条件を後から揃えるために使う
// 例: n=21 で取った計測を先頭 9 件だけで集計し直し、n=9 の計測と比較できるようにする
// run の先頭から固定件数を採るだけなので、選び方による偏りは入らない
//
// 使い方: node rerollup.ts --runs 9

import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const perfDir = path.resolve(here, '../../docs/perf');
const resultsPath = path.join(perfDir, 'results.jsonl');

const METRIC_KEYS = ['lcp', 'fcp', 'cls', 'tbt', 'si', 'ttfb', 'score'] as const;

const AUDIT_IDS: Record<string, string> = {
  lcp: 'largest-contentful-paint',
  fcp: 'first-contentful-paint',
  cls: 'cumulative-layout-shift',
  tbt: 'total-blocking-time',
  si: 'speed-index',
  ttfb: 'server-response-time',
};

function percentile(sorted: number[], ratio: number): number | null {
  if (sorted.length === 0) {
    return null;
  }

  const position = (sorted.length - 1) * ratio;
  const lower = Math.floor(position);
  const upper = Math.ceil(position);

  if (lower === upper) {
    return sorted[lower];
  }

  return sorted[lower] + (sorted[upper] - sorted[lower]) * (position - lower);
}

function round(value: number | null): number | null {
  return value === null ? null : Math.round(value * 1000) / 1000;
}

function summarize(values: number[]): { median: number | null; iqr: number | null } {
  const sorted = [...values].filter(Number.isFinite).sort((a, b) => a - b);
  const upper = percentile(sorted, 0.75);
  const lower = percentile(sorted, 0.25);

  return {
    median: round(percentile(sorted, 0.5)),
    iqr: upper === null || lower === null ? null : round(upper - lower),
  };
}

function metricOf(lhr: Record<string, any>, name: string): number {
  return name === 'score'
    ? lhr.categories?.performance?.score
    : lhr.audits?.[AUDIT_IDS[name]]?.numericValue;
}

function main(): void {
  const index = process.argv.indexOf('--runs');
  const runs = index === -1 ? 9 : Number(process.argv[index + 1]);
  const rows = readFileSync(resultsPath, 'utf8').trim().split('\n').filter(Boolean).map(line => JSON.parse(line));

  for (const row of rows) {
    const dir = path.join(perfDir, 'reports', row.label, row.formFactor, row.page);
    const loaded: Record<string, any>[] = [];

    for (let i = 1; i <= runs; i += 1) {
      const file = path.join(dir, `run-${i}.json`);

      if (!existsSync(file)) {
        break;
      }

      loaded.push(JSON.parse(readFileSync(file, 'utf8')));
    }

    if (loaded.length < runs) {
      console.log(`  スキップ (run が ${loaded.length} 件しかない): ${row.label}/${row.formFactor}/${row.page}`);
      continue;
    }

    row.runs = runs;
    row.metrics = Object.fromEntries(
      METRIC_KEYS.map(name => [name, summarize(loaded.map(lhr => metricOf(lhr, name)))]),
    );
  }

  writeFileSync(resultsPath, `${rows.map(row => JSON.stringify(row)).join('\n')}\n`);

  console.log(`${rows.length} 行を n=${runs} で再集計しました`);
}

main();
