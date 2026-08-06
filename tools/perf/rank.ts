#!/usr/bin/env node
// unlighthouse のスキャン結果を集計し、ルート種別ごとに遅いページを並べる
// ここで見つけたページを pages.json に載せて measure.ts で本計測する
// 使い方: node rank.ts ../../docs/perf/scans/latest/ci-result.json

import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

interface Route {
  path: string;
  score: number | null;
  metrics: Record<string, { numericValue?: number } | undefined>;
}

interface ScanResult {
  routes: Route[];
}

interface Group {
  label: string;
  test: (routePath: string) => boolean;
}

const here = path.dirname(fileURLToPath(import.meta.url));

// 上から順に判定し、最初に一致したグループへ入れる
// unlighthouse は末尾スラッシュを落として返すため、あってもなくても一致させる
const GROUPS: Group[] = [
  { label: '/songs/[id]/', test: p => /^\/songs\/[^/]+\/?$/.test(p) },
  { label: '/releases/[id]/', test: p => /^\/releases\/[^/]+\/?$/.test(p) },
  { label: '/media/[id]/', test: p => /^\/media\/[^/]+\/?$/.test(p) },
  { label: 'その他 (一覧・静的)', test: () => true },
];

// UUID が壊れているページを検出する
// U+FFFD (置換文字) の UTF-8 バイト列 EF BF BD が ID に混入している
// UUID のハイフンで分断されることがある (7a7a604b-4601-4967-efbf-bd45001618ef) ため
// ハイフンを除いてから判定する
function isBrokenId(routePath: string): boolean {
  return /efbfbd/.test(routePath.replace(/-/g, ''));
}

const TOP_N = 5;

function lcpOf(route: Route): number {
  return route.metrics['largest-contentful-paint']?.numericValue ?? 0;
}

function median(values: number[]): number {
  if (values.length === 0) {
    return 0;
  }

  const sorted = [...values].sort((a, b) => a - b);
  const middle = Math.floor(sorted.length / 2);

  return sorted.length % 2 === 0 ? (sorted[middle - 1] + sorted[middle]) / 2 : sorted[middle];
}

function groupOf(routePath: string): string {
  return GROUPS.find(group => group.test(routePath))!.label;
}

function report(result: ScanResult): void {
  const grouped = new Map<string, Route[]>();

  for (const route of result.routes) {
    const label = groupOf(route.path);

    grouped.set(label, [...(grouped.get(label) ?? []), route]);
  }

  const broken = result.routes.filter(route => isBrokenId(route.path));

  console.log(`${result.routes.length} ページを集計しました\n`);

  if (broken.length > 0) {
    console.log(`⚠ ID が壊れているページが ${broken.length} 件あります (例: ${broken[0].path})`);
    console.log('  UUID に U+FFFD の UTF-8 バイト列が混入しています。代表ページには選ばないでください\n');
  }

  for (const group of GROUPS) {
    const routes = grouped.get(group.label);

    if (!routes || routes.length === 0) {
      continue;
    }

    // 壊れた ID は代表に選べないので、順位からは外して表示する
    const usable = routes.filter(route => !isBrokenId(route.path));
    const worst = [...usable].sort((a, b) => lcpOf(b) - lcpOf(a));

    console.log(`## ${group.label} (${routes.length} ページ / うち ID 正常 ${usable.length})`);

    if (worst.length === 0) {
      console.log('   代表に選べるページがありません\n');
      continue;
    }

    console.log(`   LCP 中央値 ${Math.round(median(usable.map(lcpOf)))}ms / 最遅 ${Math.round(lcpOf(worst[0]))}ms`);

    for (const route of worst.slice(0, TOP_N)) {
      console.log(`   ${String(Math.round(lcpOf(route))).padStart(7)}ms  score=${route.score ?? '-'}  ${route.path}`);
    }

    console.log('');
  }
}

// 各 form factor での順位を突き合わせ、どちらでも上位のページを選ぶ
// 片方だけで遅いページは form factor 固有の事情なので代表には向かない
function reportCombined(scans: { name: string; result: ScanResult }[]): void {
  console.log(`${'='.repeat(60)}\n# 総合 (${scans.map(scan => scan.name).join(' + ')})\n${'='.repeat(60)}\n`);
  console.log('どちらでも遅い順です。pages.json の代表はここから選びます\n');

  for (const group of GROUPS) {
    // form factor ごとに「遅い順の順位」を作る
    const ranks = scans.map(scan => {
      const usable = scan.result.routes
        .filter(route => groupOf(route.path) === group.label)
        .filter(route => !isBrokenId(route.path))
        .sort((a, b) => lcpOf(b) - lcpOf(a));

      return new Map(usable.map((route, index) => [route.path.replace(/\/$/, ''), { rank: index + 1, lcp: lcpOf(route) }]));
    });

    // 全 form factor に出てくるページだけを対象にする
    const common = [...ranks[0].keys()].filter(routePath => ranks.every(rank => rank.has(routePath)));

    if (common.length === 0) {
      continue;
    }

    // 最も悪い順位で並べる。片方だけ突出しているページを上位に来させない
    const ordered = common
      .map(routePath => ({
        path: routePath,
        entries: ranks.map(rank => rank.get(routePath)!),
      }))
      .sort((a, b) => {
        const worstA = Math.max(...a.entries.map(entry => entry.rank));
        const worstB = Math.max(...b.entries.map(entry => entry.rank));

        return worstA - worstB;
      });

    console.log(`## ${group.label}`);

    for (const item of ordered.slice(0, TOP_N)) {
      const detail = item.entries
        .map((entry, index) => `${scans[index].name} ${entry.rank}位 ${Math.round(entry.lcp)}ms`)
        .join(' / ');

      console.log(`   ${detail}  ${item.path}`);
    }

    console.log('');
  }
}

async function main(): Promise<void> {
  const targets = process.argv.slice(2);

  if (targets.length === 0) {
    targets.push(path.join(here, '../../docs/perf/scans/latest/ci-result.json'));
  }

  console.log('この数値は並列クロールで下振れするため、順位を見る用途に限ります');
  console.log('比較に使う値は measure.ts で取り直してください\n');

  const scans: { name: string; result: ScanResult }[] = [];

  for (const target of targets) {
    // 出力先が <label>/<formFactor>/ci-result.json なので親ディレクトリ名が form factor になる
    const name = path.basename(path.dirname(target));
    const result: ScanResult = JSON.parse(await readFile(target, 'utf8'));

    console.log(`${'='.repeat(60)}\n# ${name}\n${'='.repeat(60)}\n`);

    report(result);
    scans.push({ name: name, result: result });
  }

  if (scans.length > 1) {
    reportCombined(scans);
  }
}

await main();
