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
