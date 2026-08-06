import { defineUnlighthouseConfig } from 'unlighthouse/config'
// 全ページを走査して「遅いページ」を見つけるための探索用スキャン
// 施策の前後比較には使わない。並列クロールで値が汚れるため、順位付けの用途に限る
// 前後比較は measure.ts が担当する (docs/perf/README.md を参照)

export default defineUnlighthouseConfig({
  site: 'https://isekaijoucho.fan',

  // 生成物は数百 MB になるため git 管理外に置く
  outputPath: '../../docs/perf/scans/latest',

  scanner: {
    // device は mise タスクが --mobile / --desktop で指定する

    // 探索なので 1 回で十分。順位が分かればよく、中央値の精度は要らない
    samples: 1,

    // 既定では /songs/[id] のような動的ルートを 5 件に間引く
    // 遅いページを取りこぼさないために全件を走査する
    dynamicSampling: false,

    // 既定の上限は 200 で、これを超えた分は黙って捨てられる
    // 実際 media 詳細 832 枚が 1 枚も走査されなかったため明示的に上げる
    maxRoutes: 2000,

    // measure.ts と条件を揃える
    throttle: true,

    robotsTxt: false,
    sitemap: true,

    exclude: ['/fragments/*/*'],
  },

  // performance 以外は探索に不要なので落とす
  lighthouseOptions: {
    onlyCategories: ['performance'],
  },

  // 22 コアだが、並列度を上げるほど値が下振れする
  // 順位の逆転を避けたいので 4 に抑える
  puppeteerClusterOptions: {
    maxConcurrency: 4,
  },
})
