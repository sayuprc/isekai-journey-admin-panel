import Elysia from 'elysia';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const songAttributes = new Elysia({ prefix: '/song-attributes' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    return resolveApiResponse(await createAuthClient(credential).GET('/song-attributes'));
  });
