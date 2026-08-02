// Home はブランドロゴから遷移できるためナビには含めない
export const navLinks = [
  { href: '/songs', label: 'Songs', jp: '楽曲' },
  { href: '/releases', label: 'Releases', jp: 'リリース' },
  // { href: '/media', label: 'Media', jp: 'メディア' },
  // { href: '/profile', label: 'Profile', jp: '人物' },
] as const;
