import { mediaServiceListMedia } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import type { Media } from './types.js';

async function all(): Promise<Media[]> {
  const media: Media[] = [];
  let cursor: string | undefined;

  while (true) {
    const { data } = await mediaServiceListMedia({
      client: apiClient,
      query: { limit: 50, cursor },
    });

    if (!data) {
      throw new Error('mediaServiceListMedia returned no data');
    }

    media.push(...data.media);

    if (!data.nextCursor) {
      break;
    }
    cursor = data.nextCursor;
  }

  return media;
}

export const mediaRepository = {
  all,
};
