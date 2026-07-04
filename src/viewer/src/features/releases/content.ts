import { getCollection } from 'astro:content';
import type { ReleaseGroup } from './types';

type ReleaseGroupCollectionItem = ReleaseGroup & {
  index: number;
};

async function entries() {
  return (await getCollection('releaseGroups'))
    .map(entry => ({ ...entry, data: entry.data as ReleaseGroupCollectionItem }))
    .sort((a, b) => a.data.index - b.data.index);
}

type ReleaseGroupCollectionEntry = Awaited<ReturnType<typeof entries>>[number];

function fromEntry(entry: ReleaseGroupCollectionEntry): ReleaseGroup {
  const { index, ...releaseGroup } = entry.data;
  void index;

  return releaseGroup;
}

async function all(): Promise<ReleaseGroup[]> {
  return (await entries()).map(fromEntry);
}

export const releaseGroupContentRepository = {
  all,
  entries,
  fromEntry,
};
