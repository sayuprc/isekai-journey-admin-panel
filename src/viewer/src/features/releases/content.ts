import { getCollection } from 'astro:content';
import type { ReleaseGroup } from './types';

type ReleaseGroupCollectionItem = ReleaseGroup & {
  index: number;
};

async function all(): Promise<ReleaseGroup[]> {
  return (await getCollection('releaseGroups'))
    .sort((a, b) => (a.data as ReleaseGroupCollectionItem).index - (b.data as ReleaseGroupCollectionItem).index)
    .map((entry) => {
      const { index, ...releaseGroup } = entry.data as ReleaseGroupCollectionItem;
      void index;

      return releaseGroup;
    });
}

export const releaseGroupContentRepository = {
  all,
};
