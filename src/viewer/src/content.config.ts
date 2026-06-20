import { defineCollection } from 'astro:content';
import { mediaRepository } from './features/media/api';
import { songRepository } from './features/songs/api';

const songs = defineCollection({
  loader: async () => (await songRepository.all()).map((song, index) => ({
    id: song.songId,
    index,
    ...song,
  })),
});

const media = defineCollection({
  loader: async () => (await mediaRepository.all()).map((mediaItem, index) => ({
    id: mediaItem.mediaId,
    index,
    ...mediaItem,
  })),
});

export const collections = {
  media,
  songs,
};
