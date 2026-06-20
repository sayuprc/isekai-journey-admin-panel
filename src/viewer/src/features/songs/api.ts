import { songServiceListSongs } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import type { Song } from './types.js';

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

export const songRepository = {
  all,
};
