import { LIGHT_PALETTES, Q, SITE_DATA, THEMES } from './site-data.js';

export { LIGHT_PALETTES, Q, SITE_DATA, THEMES };

export const navLinks = [
  { href: '/', label: 'Home', jp: '玄関' },
  { href: '/songs', label: 'Songs', jp: '楽曲' },
  { href: '/releases', label: 'Releases', jp: 'リリース' },
  { href: '/media', label: 'Media', jp: 'メディア' },
  { href: '/profile', label: 'Profile', jp: '人物' },
] as const;

export const VIDEO_TYPES = ['mv', 'live-clip', 'interview', 'short'] as const;
export const POST_TYPES = ['tweet', 'instagram', 'youtube-community', 'blog'] as const;

export function isVideo(type: string) {
  return VIDEO_TYPES.includes(type as (typeof VIDEO_TYPES)[number]);
}

export function isPost(type: string) {
  return POST_TYPES.includes(type as (typeof POST_TYPES)[number]);
}

export function mediaTypeLabel(type: string) {
  return (
    {
      'mv': 'Music Video',
      'live-clip': 'Live Clip',
      'interview': 'Interview',
      'short': 'Short',
      tweet: 'X / Twitter',
      instagram: 'Instagram',
      'youtube-community': 'YT Community',
      blog: 'Blog',
    }[type] ?? type
  );
}

export function kindLabel(kind: string) {
  return (
    {
      song: '楽曲',
      release: 'リリース',
      media: '映像',
      event: '出来事',
    }[kind] ?? kind
  );
}

export function isActivePath(currentPath: string, href: string) {
  if (href === '/') return currentPath === '/';
  return currentPath === href || currentPath.startsWith(`${href}/`);
}

export function latestRelease() {
  return [...SITE_DATA.releases].sort((a, b) => b.date.localeCompare(a.date))[0] ?? null;
}

export function latestMedia(limit = 3) {
  return [...SITE_DATA.media].filter(entry => isVideo(entry.type)).sort((a, b) => b.date.localeCompare(a.date)).slice(0, limit);
}

export function latestPosts(limit = 3) {
  return [...SITE_DATA.media].filter(entry => isPost(entry.type)).sort((a, b) => b.date.localeCompare(a.date)).slice(0, limit);
}

export function sortedSongs() {
  return [...SITE_DATA.songs].sort((a, b) => {
    const da = Q.firstAppearanceDateOfSong(a.id) ?? '0';
    const db = Q.firstAppearanceDateOfSong(b.id) ?? '0';
    return db.localeCompare(da);
  });
}

export function sortedReleases() {
  return [...SITE_DATA.releases].sort((a, b) => b.date.localeCompare(a.date));
}

export function sortedMedia() {
  return [...SITE_DATA.media].filter(entry => isVideo(entry.type)).sort((a, b) => b.date.localeCompare(a.date));
}

export function sortedPosts() {
  return [...SITE_DATA.media].filter(entry => isPost(entry.type)).sort((a, b) => b.date.localeCompare(a.date));
}
