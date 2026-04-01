import { Elysia, t } from 'elysia';
import { songTagServiceCreateSongTag, songTagServiceListSongTags } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const songTags = new Elysia({ prefix: '/song-tags' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    return resolveApiResponse(await songTagServiceListSongTags({ client: createAuthClient(credential) }));
  })
  .post(
    '/',
    async ({ body: { name }, credential }) => {
      return resolveApiResponse(await songTagServiceCreateSongTag({ client: createAuthClient(credential), body: { name } }));
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  );
