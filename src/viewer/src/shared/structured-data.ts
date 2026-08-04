// JSON-LD の組み立て
// ここはサイト全体で使う型と共通部品だけを持ち、ページ固有の構造化データは features 配下に置く

import { SITE_DESCRIPTION, SITE_TITLE } from './site';

export type JsonLd = Record<string, unknown>;

export type BreadcrumbItem = {
  label: string;
  /** 現在地は null にしてリンクを張らない */
  href: string | null;
};

/** JSON-LD の URL は絶対 URL で出す。site は astro.config で常に設定される */
export function absoluteUrl(path: string, site: URL | undefined): string {
  return new URL(path, site).href;
}

export function websiteJsonLd(site: URL | undefined): JsonLd {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    'name': SITE_TITLE,
    'description': SITE_DESCRIPTION,
    'url': absoluteUrl('/', site),
    'inLanguage': 'ja',
  };
}

export function breadcrumbListJsonLd(items: readonly BreadcrumbItem[], site: URL | undefined): JsonLd {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    'itemListElement': items.map((item, index) => ({
      '@type': 'ListItem',
      'position': index + 1,
      'name': item.label,
      ...(item.href === null ? {} : { item: absoluteUrl(item.href, site) }),
    })),
  };
}

/** script 要素へ埋め込む文字列。HTML パーサが途中で script を閉じないよう < を退避する */
export function serializeJsonLd(items: readonly JsonLd[]): string {
  return JSON.stringify(items).replaceAll('<', '\\u003c');
}
