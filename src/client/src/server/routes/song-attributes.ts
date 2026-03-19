import Elysia from 'elysia';
import { songAttributeServiceListSongAttributes } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const songAttributes = new Elysia({ prefix: '/song-attributes' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    return resolveApiResponse(await songAttributeServiceListSongAttributes({ client: createAuthClient(credential) }));
  });
