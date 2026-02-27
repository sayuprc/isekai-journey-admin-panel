import { createAuthClient } from '../client';
import { withAuth } from '../middleware';

export const songTypes = withAuth('/song-types')
  .get('/', async ({ credential }) => {
    const { data } = await createAuthClient(credential).GET('/song-types');

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  });
