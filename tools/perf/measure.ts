#!/usr/bin/env node
// viewer のパフォーマンスを本番環境で計測し docs/perf/results.jsonl に追記する
// 使い方: node measure.ts --label baseline

import { appendFileSync, mkdirSync, writeFileSync } from 'node:fs';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import lighthouse from 'lighthouse';
import puppeteer from 'puppeteer';

type FormFactor = 'mobile' | 'desktop';

interface PageConfig {
  key: string;
  path: string;
  note: string;
}

interface PagesConfig {
  origin: string;
  pages: PageConfig[];
}

interface Args {
  label?: string;
  runs: number;
  warmup: number;
  formFactor: FormFactor[];
  pages?: string[];
  config?: string;
}

interface Metrics {
  lcp: number | null;
  fcp: number | null;
  cls: number | null;
  tbt: number | null;
  si: number | null;
  ttfb: number | null;
  score: number | null;
}

interface Summary {
  median: number | null;
  iqr: number | null;
}

interface CacheHeaders {
  cacheStatus: string | null;
  age: string | null;
  encoding: string | null;
}

interface ContentCounts {
  songs: number;
  releases: number;
  media: number;
  fragments: number;
}

interface RunResult {
  lhr: Record<string, any>;
  json: string;
  html: string;
  metrics: Metrics;
}

const here = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(here, '../..');
const perfDir = path.join(repoRoot, 'docs/perf');
const resultsPath = path.join(perfDir, 'results.jsonl');

const METRIC_KEYS = ['lcp', 'fcp', 'cls', 'tbt', 'si', 'ttfb', 'score'] as const;

// スロットリング設定を明示的に固定する
// Lighthouse の既定プリセットと同値だが、既定値の変更に引きずられないようコードに書き出す
const PRESETS: Record<FormFactor, Record<string, unknown>> = {
  mobile: {
    formFactor: 'mobile',
    throttling: {
      rttMs: 150,
      throughputKbps: 1638.4,
      requestLatencyMs: 562.5,
      downloadThroughputKbps: 1474.56,
      uploadThroughputKbps: 675,
      cpuSlowdownMultiplier: 4,
    },
    screenEmulation: { mobile: true, width: 412, height: 823, deviceScaleFactor: 1.75, disabled: false },
  },
  desktop: {
    formFactor: 'desktop',
    throttling: {
      rttMs: 40,
      throughputKbps: 10240,
      requestLatencyMs: 0,
      downloadThroughputKbps: 0,
      uploadThroughputKbps: 0,
      cpuSlowdownMultiplier: 1,
    },
    screenEmulation: { mobile: false, width: 1350, height: 940, deviceScaleFactor: 1, disabled: false },
    // ヘッドレス Chrome 本来の UA を使う。モバイル UA が desktop の計測に混ざらないようにする
    emulatedUserAgent: false,
  },
};

function parseArgs(argv: string[]): Args {
  const args: Args = { runs: 9, warmup: 3, formFactor: ['mobile', 'desktop'] };

  for (let i = 0; i < argv.length; i += 1) {
    const key = argv[i];

    if (!key.startsWith('--')) {
      continue;
    }

    const value = argv[i + 1];

    i += 1;

    switch (key) {
      case '--label':
        args.label = value;
        break;
      case '--runs':
        args.runs = Number(value);
        break;
      case '--warmup':
        args.warmup = Number(value);
        break;
      case '--form-factor':
        args.formFactor = value === 'both' ? ['mobile', 'desktop'] : [value as FormFactor];
        break;
      case '--pages':
        args.pages = value.split(',');
        break;
      case '--config':
        args.config = value;
        break;
      default:
        throw new Error(`不明なオプションです: ${key}`);
    }
  }

  return args;
}

function percentile(sortedValues: number[], ratio: number): number | null {
  if (sortedValues.length === 0) {
    return null;
  }

  const position = (sortedValues.length - 1) * ratio;
  const lower = Math.floor(position);
  const upper = Math.ceil(position);

  if (lower === upper) {
    return sortedValues[lower];
  }

  return sortedValues[lower] + (sortedValues[upper] - sortedValues[lower]) * (position - lower);
}

function round(value: number | null): number | null {
  return value === null ? null : Math.round(value * 1000) / 1000;
}

function summarize(values: (number | null)[]): Summary {
  const usable = values.filter((value): value is number => typeof value === 'number' && Number.isFinite(value));

  if (usable.length === 0) {
    return { median: null, iqr: null };
  }

  const sorted = [...usable].sort((a, b) => a - b);
  const upper = percentile(sorted, 0.75);
  const lower = percentile(sorted, 0.25);

  return {
    median: round(percentile(sorted, 0.5)),
    iqr: upper === null || lower === null ? null : round(upper - lower),
  };
}

function metricsOf(lhr: Record<string, any>): Metrics {
  const numeric = (id: string): number | null => lhr.audits?.[id]?.numericValue ?? null;

  return {
    lcp: numeric('largest-contentful-paint'),
    fcp: numeric('first-contentful-paint'),
    cls: numeric('cumulative-layout-shift'),
    tbt: numeric('total-blocking-time'),
    si: numeric('speed-index'),
    ttfb: numeric('server-response-time'),
    score: lhr.categories?.performance?.score ?? null,
  };
}

// network-requests から resourceType ごとに転送量 (圧縮後) と実サイズ (圧縮前) を集計する
// 本番は zstd / brotli が効くので、両方を残さないと issue に書かれた生サイズの削減率と比較できない
function resourcesOf(lhr: Record<string, any>): {
  transfer: Record<string, number>;
  resource: Record<string, number>;
  requests: number;
} {
  const items: Record<string, any>[] = lhr.audits?.['network-requests']?.details?.items ?? [];
  const transfer: Record<string, number> = {};
  const resource: Record<string, number> = {};

  for (const item of items) {
    const type = (item.resourceType as string | undefined) ?? 'Other';

    transfer[type] = (transfer[type] ?? 0) + (item.transferSize ?? 0);
    resource[type] = (resource[type] ?? 0) + (item.resourceSize ?? 0);
  }

  return { transfer, resource, requests: items.length };
}

// クリティカルリクエストチェーンの深さを測る
// Lighthouse 13 で critical-request-chains は network-dependency-tree-insight に置き換わった
// #923 (フォントの 4 段直列チェーン) の効果はこの値の減少として出る
function criticalChainDepthOf(lhr: Record<string, any>): number | null {
  const sections: Record<string, any>[] = lhr.audits?.['network-dependency-tree-insight']?.details?.items ?? [];
  const chains = sections.find(item => item.value?.type === 'network-tree')?.value?.chains;

  if (!chains) {
    return null;
  }

  const depthOf = (nodes: Record<string, any>): number => {
    let deepest = 0;

    for (const node of Object.values(nodes)) {
      deepest = Math.max(deepest, 1 + depthOf(node.children ?? {}));
    }

    return deepest;
  };

  return depthOf(chains);
}

// SSG なので計測期間中にコンテンツが増えると転送量が動く
// 施策の効果と混ざらないよう、計測時点の件数を sitemap から数えて必ず記録する
async function contentCounts(origin: string): Promise<ContentCounts> {
  const response = await fetch(`${origin}/sitemap-0.xml`);

  if (!response.ok) {
    throw new Error(`sitemap の取得に失敗しました: HTTP ${response.status}`);
  }

  const xml = await response.text();
  const count = (segment: string): number => (xml.match(new RegExp(`${origin}/${segment}/[^<]`, 'g')) ?? []).length;

  return {
    songs: count('songs'),
    releases: count('releases'),
    media: count('media'),
    fragments: count('fragments'),
  };
}

// HTML からサブリソースの URL を集める
// srcset は "url 320w, url 640w" 形式なので候補ごとに URL 部分だけを取り出す
function subresourceUrlsOf(html: string, origin: string): string[] {
  const found = new Set<string>();

  for (const [, value] of html.matchAll(/(?:\bsrc|\bhref)="([^"]+)"/g)) {
    found.add(value);
  }

  for (const [, value] of html.matchAll(/\bsrcset="([^"]+)"/g)) {
    for (const candidate of value.split(',')) {
      const url = candidate.trim().split(/\s+/)[0];

      if (url) {
        found.add(url);
      }
    }
  }

  return [...found]
    .filter(url => !url.startsWith('data:') && !url.startsWith('#') && !url.endsWith('/'))
    .map(url => (url.startsWith('http') ? url : new URL(url, origin).toString()));
}

async function fetchAll(urls: string[], concurrency: number): Promise<void> {
  const queue = [...urls];

  const worker = async (): Promise<void> => {
    for (let url = queue.pop(); url !== undefined; url = queue.pop()) {
      try {
        const response = await fetch(url, { headers: { accept: 'image/avif,image/webp,image/*,*/*' } });

        await response.arrayBuffer();
      } catch {
        // 温めるだけなので失敗は無視する
      }
    }
  };

  await Promise.all(Array.from({ length: concurrency }, worker));
}

// デプロイ直後の初回リクエストはコールドで TTFB が跳ねる
// 本計測の前に暖めたうえで、キャッシュ状態を示すヘッダを記録して回ごとの条件差を検出できるようにする
//
// HTML だけでなくサブリソースも暖める必要がある
// Cloudflare Image Transformations は variant ごとに初回リクエストで生成されるため、
// HTML しか暖めないと画像がコールドのまま計測され、LCP が数秒単位で悪化する
// (実測で同一 variant がコールド 0.71s / ウォーム 0.19s)
async function warmup(url: string, times: number, origin: string): Promise<CacheHeaders> {
  let headers: CacheHeaders = { cacheStatus: null, age: null, encoding: null };
  let html = '';

  for (let i = 0; i < times; i += 1) {
    const response = await fetch(url, { headers: { 'accept-encoding': 'gzip, br, zstd' } });

    html = await response.text();

    headers = {
      cacheStatus: response.headers.get('cf-cache-status'),
      age: response.headers.get('age'),
      encoding: response.headers.get('content-encoding'),
    };
  }

  // 2 周するのは、1 周目で生成された variant を確実に HIT 状態にするため
  const subresources = subresourceUrlsOf(html, origin);

  await fetchAll(subresources, 8);
  await fetchAll(subresources, 8);

  return headers;
}

// run ごとにブラウザを立て直す
//
// Lighthouse の storage reset も CDP の Network.clearBrowserCache も HTTP キャッシュを消しきれず、
// 2 回目以降は first-party の immutable 資産がキャッシュから読まれて transferSize がヘッダ分だけになる
// (self-host した CSS 98KB が 0.4KB として記録された)
// simulate はその transferSize からダウンロード時間を計算するため、転送量だけでなく LCP と FCP まで
// 楽観側にずれる。全ての run を初回訪問と同じ条件に揃えるにはプロセスごと分けるしかない
async function measurePage(url: string, formFactor: FormFactor, runs: number): Promise<RunResult[]> {
  const results: RunResult[] = [];

  for (let i = 0; i < runs; i += 1) {
    const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox', '--disable-dev-shm-usage'] });

    try {
      const runner = await lighthouse(
        url,
        { port: Number(new URL(browser.wsEndpoint()).port), output: ['json', 'html'], logLevel: 'error' },
        { extends: 'lighthouse:default', settings: { onlyCategories: ['performance'], throttlingMethod: 'simulate', ...PRESETS[formFactor] } } as any,
      );

      if (!runner) {
        throw new Error(`Lighthouse の実行に失敗しました: ${url}`);
      }

      const lhr = runner.lhr as unknown as Record<string, any>;
      const metrics = metricsOf(lhr);

      results.push({ lhr: lhr, json: runner.report[0], html: runner.report[1], metrics: metrics });

      process.stdout.write(`    run ${i + 1}/${runs}: LCP ${Math.round(metrics.lcp ?? 0)}ms\n`);
    } finally {
      await browser.close();
    }
  }

  return results;
}

async function main(): Promise<void> {
  const args = parseArgs(process.argv.slice(2));

  if (!args.label) {
    throw new Error('--label は必須です (例: --label baseline, --label 921-logo-svg)');
  }

  const config: PagesConfig = JSON.parse(await readFile(args.config ?? path.join(here, 'pages.json'), 'utf8'));
  const targets = args.pages ? config.pages.filter(page => args.pages?.includes(page.key)) : config.pages;

  if (targets.length === 0) {
    throw new Error('計測対象のページがありません');
  }

  const measuredAt = new Date().toISOString();
  const counts = await contentCounts(config.origin);

  console.log(`label=${args.label} origin=${config.origin} runs=${args.runs} formFactor=${args.formFactor.join(',')}`);
  console.log(`content: songs=${counts.songs} releases=${counts.releases} media=${counts.media}`);

  mkdirSync(perfDir, { recursive: true });

  {
    for (const formFactor of args.formFactor) {
      for (const page of targets) {
        const url = `${config.origin}${page.path}`;

        console.log(`\n[${formFactor}/${page.key}] ${url}`);

        const headers = await warmup(url, args.warmup, config.origin);

        console.log(`  warmup: cf-cache-status=${headers.cacheStatus} encoding=${headers.encoding} (サブリソースも暖め済み)`);

        const results = await measurePage(url, formFactor, args.runs);

        // 中央値の回を代表として残す。レポートを後から Lighthouse Viewer で開き直すため
        const ordered = [...results].sort((a, b) => (a.metrics.lcp ?? 0) - (b.metrics.lcp ?? 0));
        const representative = ordered[Math.floor(ordered.length / 2)];
        const reportDir = path.join(perfDir, 'reports', args.label, formFactor, page.key);

        mkdirSync(reportDir, { recursive: true });

        results.forEach((result, index) => {
          writeFileSync(path.join(reportDir, `run-${index + 1}.json`), result.json);
        });

        writeFileSync(path.join(reportDir, 'median.html'), representative.html);

        const summary = {
          label: args.label,
          formFactor: formFactor,
          page: page.key,
          url: url,
          measuredAt: measuredAt,
          runs: args.runs,
          headers: headers,
          counts: counts,
          metrics: Object.fromEntries(
            METRIC_KEYS.map(name => [name, summarize(results.map(result => result.metrics[name]))]),
          ),
          // 転送量とチェーンの深さは run 1 から採る
          // 2 回目以降は first-party の immutable 資産が Chrome のキャッシュから読まれ、
          // transferSize がヘッダ分だけになる (self-host した CSS で 98KB が 0.4KB として記録された)
          // 初回訪問の実態を残したいので、キャッシュが空の run 1 を代表にする
          ...resourcesOf(results[0].lhr),
          criticalChainDepth: criticalChainDepthOf(results[0].lhr),
        };

        appendFileSync(resultsPath, `${JSON.stringify(summary)}\n`);

        const lcp = summary.metrics.lcp;

        console.log(`  => LCP ${lcp.median}ms (IQR ${lcp.iqr}) / ${path.relative(repoRoot, reportDir)}`);
      }
    }
  }

  console.log(`\n${path.relative(repoRoot, resultsPath)} に追記しました`);
}

await main();
