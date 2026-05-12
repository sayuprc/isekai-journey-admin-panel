import { type Release, SITE_DATA } from '../../data/mock/site-data';

export function sortedReleases(): Release[] {
  return [...SITE_DATA.releases].sort((a, b) => b.date.localeCompare(a.date));
}

export function latestRelease(): Release | null {
  return [...SITE_DATA.releases].sort((a, b) => b.date.localeCompare(a.date))[0] ?? null;
}
