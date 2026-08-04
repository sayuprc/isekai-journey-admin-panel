import { ARTIST_NAME } from '../../shared/site';
import { absoluteUrl, type JsonLd } from '../../shared/structured-data';
import type { Song } from './types';

type SongJsonLdContext = {
  site: URL | undefined;
  description: string;
  imageUrl: string | null;
};

/**
 * 楽曲詳細の MusicRecording
 * 作詞・作曲・編曲のクレジットは画面には出すが、ここには出さない
 * 非公式サイトが第三者について機械可読な主張を撒く形になり、誤りがあっても本人に訂正手段がないため
 * byArtist はサイトの主題そのもので、サイト名と description で既に明示している主張なので残す
 */
export function songJsonLd(song: Song, { site, description, imageUrl }: SongJsonLdContext): JsonLd {
  return {
    '@context': 'https://schema.org',
    '@type': 'MusicRecording',
    'name': song.title,
    'url': absoluteUrl(`/songs/${song.songId}`, site),
    'description': description,
    ...(imageUrl === null ? {} : { image: absoluteUrl(imageUrl, site) }),
    'byArtist': { '@type': 'MusicGroup', 'name': ARTIST_NAME },
    ...(song.releaseGroups.length > 0
      ? {
          inAlbum: song.releaseGroups.map(releaseGroup => ({
            '@type': 'MusicAlbum',
            'name': releaseGroup.title,
            'url': absoluteUrl(`/releases/${releaseGroup.releaseGroupId}`, site),
          })),
        }
      : {}),
  };
}
