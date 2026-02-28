import { Elysia } from 'elysia';
import { createAuthClient } from '../client';
import { authGuard } from '../middleware';

export const songTypes = new Elysia({ prefix: '/song-types' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    const { data } = await createAuthClient(credential).GET('/song-types');

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  });
