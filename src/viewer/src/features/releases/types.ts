import type { ReleaseFormat, ReleaseGroupListItem, ReleaseListItem, ReleaseMediumItem, ReleaseTrackItem } from '../../generated/types.gen.js';

export type ReleaseGroup = ReleaseGroupListItem;

export type Release = ReleaseListItem;

export type ReleaseMedium = ReleaseMediumItem;

export type ReleaseTrack = ReleaseTrackItem;

/** 代表ジャケット: 公開リリースを発売日順に見て最初に設定されているもの。 */
export function representativeJacketArtUrl(releaseGroup: ReleaseGroup): string | null {
  return releaseGroup.releases.find(release => release.jacketArtUrl !== null)?.jacketArtUrl ?? null;
}

/** グループ全体の提供形態（傘下リリースの和集合、値順）。 */
export function groupFormats(releaseGroup: ReleaseGroup): ReleaseFormat[] {
  const formatByValue = new Map<number, ReleaseFormat>();

  for (const release of releaseGroup.releases) {
    for (const format of release.formats) {
      formatByValue.set(format.value, format);
    }
  }

  return [...formatByValue.values()].toSorted((a, b) => a.value - b.value);
}

/** グループ全体の収録曲数（版をまたいだ重複は除く）。タイトルのみトラック（songId: null）はタイトルで重複排除する。 */
export function distinctTrackCount(releaseGroup: ReleaseGroup): number {
  const trackKeys = new Set(
    releaseGroup.releases.flatMap(release =>
      release.media.flatMap(medium =>
        medium.tracks.map(track => (track.songId !== null ? `song:${track.songId}` : `title:${track.title}`)),
      ),
    ),
  );

  return trackKeys.size;
}
