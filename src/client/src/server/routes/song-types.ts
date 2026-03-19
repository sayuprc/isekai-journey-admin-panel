import Elysia from 'elysia';
import { songTypeServiceListSongTypes } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const songTypes = new Elysia({ prefix: '/song-types' }).use(authGuard).get('/', async ({ credential }) => {
  return resolveApiResponse(await songTypeServiceListSongTypes({ client: createAuthClient(credential) }));
});
