import { Elysia } from 'elysia';
import { ApiError } from './errors';
import { adminUsers } from './routes/admin-users';
import { auth } from './routes/auth';
import { creators } from './routes/creators';
import { performers } from './routes/performers';
import { songAttributes } from './routes/song-attributes';
import { songTags } from './routes/song-tags';
import { songTypes } from './routes/song-types';
import { songs } from './routes/songs';

export const app = new Elysia({ prefix: '/api' })
  .onError(({ error, set }) => {
    if (error instanceof ApiError) {
      set.status = error.status;
      return error.body;
    }
  })
  .use(auth)
  .use(adminUsers)
  .use(creators)
  .use(performers)
  .use(songTypes)
  .use(songAttributes)
  .use(songTags)
  .use(songs);

export type App = typeof app;
