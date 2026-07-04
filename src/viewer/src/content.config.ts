import { defineCollection } from 'astro:content';
import { mediaRepository } from './features/media/api';
import { releaseGroupRepository } from './features/releases/api';
import { songRepository } from './features/songs/api';

const songs = defineCollection({
  loader: async () => (await songRepository.all()).map((song, index) => ({
    id: song.songId,
    index,
    ...song,
  })),
});

const releaseGroups = defineCollection({
  loader: async () => (await releaseGroupRepository.all()).map((releaseGroup, index) => ({
    id: releaseGroup.releaseGroupId,
    index,
    ...releaseGroup,
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
  releaseGroups,
  songs,
};
