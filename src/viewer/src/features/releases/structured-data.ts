import { absoluteUrl, type JsonLd } from '../../shared/structured-data';
import { isLinkableTrack, type ReleaseGroup, representativeJacketArtUrl } from './types';

type ReleaseGroupJsonLdContext = {
  site: URL | undefined;
  description: string;
};

/** schema.org の MusicAlbumReleaseType へ寄せる。その他 (99) は対応する語がないので出さない */
function albumReleaseType(value: ReleaseGroup['type']['value']): string | null {
  switch (value) {
    case 1:
      return 'SingleRelease';
    case 2:
      return 'AlbumRelease';
    case 3:
      return 'EPRelease';
    default:
      return null;
  }
}

/** リリース詳細の MusicAlbum。収録曲は画面と同じく代表リリースのトラックリストを使う */
export function releaseGroupJsonLd(releaseGroup: ReleaseGroup, { site, description }: ReleaseGroupJsonLdContext): JsonLd {
  const tracks = releaseGroup.releases[0]?.media.flatMap(medium => medium.tracks) ?? [];
  const imageUrl = representativeJacketArtUrl(releaseGroup);
  const releaseType = albumReleaseType(releaseGroup.type.value);

  return {
    '@context': 'https://schema.org',
    '@type': 'MusicAlbum',
    'name': releaseGroup.title,
    'url': absoluteUrl(`/releases/${releaseGroup.releaseGroupId}`, site),
    'description': description,
    'datePublished': releaseGroup.firstReleasedOn,
    ...(imageUrl === null ? {} : { image: absoluteUrl(imageUrl, site) }),
    ...(releaseType === null ? {} : { albumReleaseType: `https://schema.org/${releaseType}` }),
    ...(tracks.length > 0
      ? {
          numTracks: tracks.length,
          track: tracks.map(track => ({
            '@type': 'MusicRecording',
            'name': track.title,
            ...(isLinkableTrack(track) ? { url: absoluteUrl(`/songs/${track.songId}`, site) } : {}),
          })),
        }
      : {}),
  };
}
