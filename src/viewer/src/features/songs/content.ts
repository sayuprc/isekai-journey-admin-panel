import { getCollection } from 'astro:content';
import type { Song } from './types';

type SongCollectionItem = Song & {
  index: number;
};

async function all(): Promise<Song[]> {
  return (await getCollection('songs'))
    .sort((a, b) => (a.data as SongCollectionItem).index - (b.data as SongCollectionItem).index)
    .map((entry) => {
      const { index, ...song } = entry.data as SongCollectionItem;
      void index;

      return song;
    });
}

export const songContentRepository = {
  all,
};
