// Home はブランドロゴから遷移できるためナビには含めない
export const navLinks = [
  { href: '/songs', label: '楽曲' },
  { href: '/releases', label: 'リリース' },
  // { href: '/media', label: 'メディア' },
  // { href: '/profile', label: '人物' },
] as const;
