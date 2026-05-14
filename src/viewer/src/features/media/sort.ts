import { type MediaEntry, SITE_DATA } from '../../data/mock/site-data';
import { isPost, isVideo } from './labels.js';

export function latestMedia(limit = 3): MediaEntry[] {
  return [...SITE_DATA.media]
    .filter(entry => isVideo(entry.type))
    .sort((a, b) => b.date.localeCompare(a.date))
    .slice(0, limit);
}

export function latestPosts(limit = 3): MediaEntry[] {
  return [...SITE_DATA.media]
    .filter(entry => isPost(entry.type))
    .sort((a, b) => b.date.localeCompare(a.date))
    .slice(0, limit);
}
