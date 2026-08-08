// アセット画像の配信 URL
// Cloudflare の画像変換はアセット用ゾーンでのみ効くので、そこに載っていない URL は素通しする

/**
 * 変換を掛けられるオリジン
 * ローカルの MinIO は http で配信され変換も効かないため、https のときだけ対象にする
 */
const transformableOrigin = ((): string | null => {
  const assetsUrl = import.meta.env.ASSETS_URL;

  if (!assetsUrl) {
    return null;
  }

  try {
    const parsed = new URL(assetsUrl);

    return parsed.protocol === 'https:' ? parsed.origin : null;
  } catch {
    return null;
  }
})();

/**
 * 画像の配信元へ事前接続するためのオリジン
 * 画像が 1 枚しかないページでは DNS + TCP + TLS が LCP のクリティカルパスに丸ごと乗るため、
 * head の preconnect で前倒しする
 * 変換が効かない環境では別オリジンを使わないので null になる
 */
export const preconnectOrigin = transformableOrigin;

function transformableUrl(imageUrl: string): URL | null {
  if (transformableOrigin === null) {
    return null;
  }

  try {
    const parsed = new URL(imageUrl);

    return parsed.origin === transformableOrigin ? parsed : null;
  } catch {
    return null;
  }
}

/** 指定幅への変換 URL。切り抜きは表示側の object-fit に任せ、幅と形式だけ指定する */
export function assetImageUrl(imageUrl: string, width: number): string {
  const parsed = transformableUrl(imageUrl);

  return parsed === null ? imageUrl : `${parsed.origin}/cdn-cgi/image/width=${width},format=auto${parsed.pathname}`;
}

/** 変換できない URL では undefined を返し、srcset 属性ごと出さない */
export function assetImageSrcset(imageUrl: string, widths: readonly number[]): string | undefined {
  if (transformableUrl(imageUrl) === null) {
    return undefined;
  }

  return widths.map(width => `${assetImageUrl(imageUrl, width)} ${width}w`).join(', ');
}

/**
 * ジャケット画像の variant
 *
 * 種類を絞るほど同じ variant が暖まっている確率が上がる
 * 実測した表示枠から必要な device px は最大 651 (一覧カード 372px を DPR 1.75 で表示) なので
 * この 2 段で全ての枠を賄える
 */
export const JACKET_WIDTHS = [320, 640] as const;
