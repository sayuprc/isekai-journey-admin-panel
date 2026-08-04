import { ARTIST_NAME } from '../../shared/site';
import { absoluteUrl, type JsonLd } from '../../shared/structured-data';
import type { Song } from './types';

type SongJsonLdContext = {
  site: URL | undefined;
  description: string;
  imageUrl: string | null;
};

const person = (name: string): JsonLd => ({ '@type': 'Person', 'name': name });

/** 楽曲詳細の MusicRecording。作家クレジットは楽曲そのものではなく MusicComposition 側に置く */
export function songJsonLd(song: Song, { site, description, imageUrl }: SongJsonLdContext): JsonLd {
  const credits: JsonLd = {
    ...(song.composers.length > 0 ? { composer: song.composers.map(person) } : {}),
    ...(song.lyricists.length > 0 ? { lyricist: song.lyricists.map(person) } : {}),
    ...(song.arrangers.length > 0 ? { contributor: song.arrangers.map(person) } : {}),
  };

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
    ...(Object.keys(credits).length > 0
      ? { recordingOf: { '@type': 'MusicComposition', 'name': song.title, ...credits } }
      : {}),
  };
}
