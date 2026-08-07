#!/usr/bin/env node
// 保存済みの run レポートから run 単位の指標を runs.jsonl に抽出する
//
// docs/perf/reports/ は 1 計測あたり数百 MB あり git に入れられないが、
// 中央値と IQR だけの results.jsonl では分布が追えない
// (run 1 だけコールドで外れる、二峰性になる、といった性質が見えなくなる)
// 指標と LCP 要素だけを抜き出せば 1MB 未満に収まる
//
// 使い方: node extract-runs.ts

import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const perfDir = path.resolve(here, '../../docs/perf');

// 1 ページあたりの最大 run 数。実際に存在するファイルだけを読む
const MAX_RUNS = 21;

const AUDIT_IDS: Record<string, string> = {
  lcp: 'largest-contentful-paint',
  fcp: 'first-contentful-paint',
  cls: 'cumulative-layout-shift',
  tbt: 'total-blocking-time',
  si: 'speed-index',
  ttfb: 'server-response-time',
};

// LCP 要素が画像かテキストかで施策の効き方が変わるため、セレクタの末尾だけ残す
function lcpElementOf(lhr: Record<string, any>): string | null {
  const details = JSON.stringify(lhr.audits?.['lcp-discovery-insight']?.details ?? {});
  const matched = details.match(/"selector":"((?:[^"\\]|\\.)*)"/);

  return matched === null ? null : matched[1].split('>').pop()!.trim();
}

function main(): void {
  const results = readFileSync(path.join(perfDir, 'results.jsonl'), 'utf8')
    .trim()
    .split('\n')
    .filter(Boolean)
    .map(line => JSON.parse(line));

  const rows: Record<string, unknown>[] = [];

  for (const result of results) {
    for (let run = 1; run <= MAX_RUNS; run += 1) {
      const file = path.join(perfDir, 'reports', result.label, result.formFactor, result.page, `run-${run}.json`);

      if (!existsSync(file)) {
        continue;
      }

      const lhr = JSON.parse(readFileSync(file, 'utf8'));
      const metrics = Object.fromEntries(
        Object.entries(AUDIT_IDS).map(([name, id]) => [name, lhr.audits?.[id]?.numericValue ?? null]),
      );

      rows.push({
        label: result.label,
        formFactor: result.formFactor,
        page: result.page,
        run: run,
        ...metrics,
        score: lhr.categories?.performance?.score ?? null,
        lcpElement: lcpElementOf(lhr),
      });
    }
  }

  writeFileSync(path.join(perfDir, 'runs.jsonl'), `${rows.map(row => JSON.stringify(row)).join('\n')}\n`);

  console.log(`${rows.length} run を docs/perf/runs.jsonl に書き出しました`);
}

main();
