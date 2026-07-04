import type { ReleaseGroupListItem, ReleaseListItem, ReleaseMediumItem, ReleaseTrackItem } from '../../generated/types.gen.js';

export type ReleaseGroup = ReleaseGroupListItem;

export type Release = ReleaseListItem;

export type ReleaseMedium = ReleaseMediumItem;

export type ReleaseTrack = ReleaseTrackItem;

/** 代表ジャケット: 公開リリースを発売日順に見て最初に設定されているもの。 */
export function representativeJacketArtUrl(releaseGroup: ReleaseGroup): string | null {
  return releaseGroup.releases.find(release => release.jacketArtUrl !== null)?.jacketArtUrl ?? null;
}

/** グループ全体の収録曲数（版をまたいだ重複は除く）。 */
export function distinctTrackCount(releaseGroup: ReleaseGroup): number {
  const songIds = new Set(
    releaseGroup.releases.flatMap(release => release.media.flatMap(medium => medium.tracks.map(track => track.songId))),
  );

  return songIds.size;
}
