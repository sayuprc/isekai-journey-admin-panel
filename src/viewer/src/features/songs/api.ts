import { songServiceGetSong, songServiceListSongs } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { apiLimit } from '../../shared/api/concurrency.js';
import type { Song, SongDetail } from './types.js';

async function all(): Promise<Song[]> {
  const songs: Song[] = [];
  let cursor: string | undefined;

  while (true) {
    const { data, error, response } = await songServiceListSongs({
      client: apiClient,
      query: { limit: 50, cursor },
    });

    if (!data) {
      throw new Error(`songServiceListSongs failed: HTTP ${response.status} ${JSON.stringify(error)}`);
    }

    songs.push(...data.songs);

    if (!data.nextCursor) {
      break;
    }
    cursor = data.nextCursor;
  }

  return songs;
}

async function get(songId: string): Promise<SongDetail> {
  return apiLimit(async () => {
    const { data, error, response } = await songServiceGetSong({
      client: apiClient,
      path: { songId },
    });

    if (!data) {
      throw new Error(`songServiceGetSong failed: HTTP ${response.status} ${JSON.stringify(error)}`);
    }

    return data.song;
  });
}

export const songRepository = {
  all,
  get,
};
