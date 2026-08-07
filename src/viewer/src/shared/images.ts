/**
 * Cloudflare Image Transformations を通した画像 URL を組み立てる
 * APP_ENV が local のときは /cdn-cgi/image/ が存在しないため元 URL を素通しする
 */

/*
 * variant は少なくする
 * 変換は variant ごとに初回リクエストで生成され、そのとき約 0.45 秒の固定コストがかかる
 * (実測: コールド 0.46〜0.79s / ウォーム 0.19s。原本サイズによる差は 0.2s 程度しかない)
 * 種類を絞るほど同じ variant が暖まっている確率が上がる
 *
 * 実測した表示枠から必要な device px は最大 651 (一覧カード 372px を DPR 1.75 で表示)
 * したがって 320 と 640 の 2 段で全ての枠を賄える
 */
const TRANSFORM_WIDTHS = [320, 640] as const;

const isTransformEnabled = import.meta.env.APP_ENV !== 'local';

export function cdnImageUrl(url: string, width: number): string {
  if (!isTransformEnabled) {
    return url;
  }

  return `/cdn-cgi/image/width=${width},format=auto,quality=80/${url}`;
}

export function cdnImageSrcset(url: string): string | undefined {
  if (!isTransformEnabled) {
    return undefined;
  }

  return TRANSFORM_WIDTHS.map(width => `${cdnImageUrl(url, width)} ${width}w`).join(', ');
}
