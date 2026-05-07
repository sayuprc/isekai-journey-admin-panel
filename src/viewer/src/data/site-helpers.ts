import { Q, SITE_DATA } from './site-data.js';

export { Q, SITE_DATA };

export const navLinks = [
  { href: '/', label: 'Home', jp: '玄関' },
  { href: '/songs', label: 'Songs', jp: '楽曲' },
  { href: '/releases', label: 'Releases', jp: 'リリース' },
  { href: '/media', label: 'Media', jp: '映像' },
  { href: '/events', label: 'Events', jp: '催し' },
  { href: '/profile', label: 'Profile', jp: '人物' },
] as const;

export const palettes = ['moon', 'gunjou', 'reimei', 'setsuya', 'hisui'] as const;

export function mediaTypeLabel(type: string) {
  return (
    {
      'mv': 'Music Video',
      'live-clip': 'Live Clip',
      'interview': 'Interview',
      'short': 'Short',
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
  return [...SITE_DATA.media].sort((a, b) => b.date.localeCompare(a.date)).slice(0, limit);
}

export function upcomingEvent() {
  return SITE_DATA.events.find(event => event.status === '予定') ?? null;
}

export function sortedSongs() {
  return [...SITE_DATA.songs].sort((a, b) => {
    const da = Q.firstReleaseDateOfSong(a.id) ?? '0';
    const db = Q.firstReleaseDateOfSong(b.id) ?? '0';
    return db.localeCompare(da);
  });
}

export function sortedReleases() {
  return [...SITE_DATA.releases].sort((a, b) => b.date.localeCompare(a.date));
}

export function sortedMedia() {
  return [...SITE_DATA.media].sort((a, b) => b.date.localeCompare(a.date));
}

export function sortedEvents() {
  return [...SITE_DATA.events].sort((a, b) => b.date.localeCompare(a.date));
}
