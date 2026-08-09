import { Vibrant } from 'node-vibrant/browser';

export const SWATCH_ORDER = [
  'Vibrant',
  'Muted',
  'DarkVibrant',
  'DarkMuted',
  'LightVibrant',
  'LightMuted',
] as const;

export type SwatchName = (typeof SWATCH_ORDER)[number];

export interface ColorCandidate {
  name: SwatchName;
  hex: string;
}

export interface ExtractedColors {
  candidates: ColorCandidate[];
  defaultHex: string;
}

/** コントラクト `^#[0-9a-f]{6}$` に揃える */
export const normalizeHex = (value: string): string | null => {
  const trimmed = value.trim().toLowerCase();
  const withHash = trimmed.startsWith('#') ? trimmed : `#${trimmed}`;

  return /^#[0-9a-f]{6}$/.test(withHash) ? withHash : null;
};

/**
 * ローカル画像から Vibrant パレットを取る。
 * 画像は object URL 経由でブラウザ内だけ読み、呼び出し側が破棄する前提。
 */
export const extractColorsFromImage = async (file: Blob): Promise<ExtractedColors> => {
  const objectUrl = URL.createObjectURL(file);

  try {
    const palette = await Vibrant.from(objectUrl).getPalette();
    const seen = new Set<string>();
    const candidates: ColorCandidate[] = [];

    for (const name of SWATCH_ORDER) {
      const swatch = palette[name];
      if (!swatch) {
        continue;
      }

      const hex = normalizeHex(swatch.hex);
      if (!hex || seen.has(hex)) {
        continue;
      }

      seen.add(hex);
      candidates.push({ name, hex });
    }

    const first = candidates[0];
    if (!first) {
      throw new Error('No color candidates extracted');
    }

    const defaultHex = candidates.find(candidate => candidate.name === 'Vibrant')?.hex ?? first.hex;

    return { candidates, defaultHex };
  } finally {
    URL.revokeObjectURL(objectUrl);
  }
};
