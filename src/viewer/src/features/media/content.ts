import { getCollection } from 'astro:content';
import type { Media } from './types';

type MediaCollectionItem = Media & {
  index: number;
};

async function all(): Promise<Media[]> {
  return (await getCollection('media'))
    .sort((a, b) => (a.data as MediaCollectionItem).index - (b.data as MediaCollectionItem).index)
    .map((entry) => {
      const { index, ...media } = entry.data as MediaCollectionItem;
      void index;

      return media;
    });
}

export const mediaContentRepository = {
  all,
};
