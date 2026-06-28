// カラーテーマ定義
// 衣装をベースにした配色のメタデータ
// 実際の色値は src/styles/viewer.css の [data-palette="..."] が Source of Truth で、ここは value / 表示名 / swatch を保持する

export type ThemeTone = 'dark' | 'light';

export type Theme = {
  value: string;
  jp: string;
  en: string;
  tone: ThemeTone;
  costume: string | null;
  swatch: [string, string, string, string];
};

export const THEMES: Theme[] = [
  {
    value: 'anemone-1',
    jp: 'アネモネ Ⅰ',
    en: 'Anemone I',
    tone: 'dark',
    costume: null,
    swatch: ['#0E0E10', '#D31B28', '#FFFFFF', '#1C1C21'],
  },
  {
    value: 'anemone-2',
    jp: 'アネモネ Ⅱ',
    en: 'Anemone II',
    tone: 'light',
    costume: null,
    swatch: ['#F3EEEE', '#60C3D0', '#4D4E88', '#E2D9DC'],
  },
  {
    value: 'nemophila-1',
    jp: 'ネモフィラ Ⅰ',
    en: 'Nemophila I',
    tone: 'light',
    costume: null,
    swatch: ['#F5F7FA', '#1C8BF4', '#22252A', '#D2E6FF'],
  },
  {
    value: 'nemophila-2',
    jp: 'ネモフィラ Ⅱ',
    en: 'Nemophila II',
    tone: 'dark',
    costume: null,
    swatch: ['#111318', '#1D70EC', '#D4D9E2', '#1C2029'],
  },
  {
    value: 'sunflower-1',
    jp: 'サンフラワー',
    en: 'Sunflower',
    tone: 'light',
    costume: null,
    swatch: ['#FAF9F6', '#FAD423', '#317781', '#FEF9C3'],
  },
  // {
  //   value: 'sunflower-2',
  //   jp: 'サンフラワー Ⅱ',
  //   en: 'Sunflower II',
  //   tone: 'dark',
  //   costume: null,
  //   swatch: ['#0F0F11', '#C5A358', '#DC2626', '#222227'],
  // },
];

export const LIGHT_PALETTES: Set<string> = new Set(
  THEMES.filter(theme => theme.tone === 'light').map(theme => theme.value),
);
