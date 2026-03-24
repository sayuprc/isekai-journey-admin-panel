import { Elysia, t } from 'elysia';
import { songTagServiceCreateSongTag } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const songTags = new Elysia({ prefix: '/song-tags' })
  .use(authGuard)
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
