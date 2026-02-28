import { Elysia } from 'elysia';
import { adminUsers } from './routes/admin-users';
import { auth } from './routes/auth';
import { creators } from './routes/creators';
import { performers } from './routes/performers';
import { songTypes } from './routes/song-types';
import { songs } from './routes/songs';

export const app = new Elysia({ prefix: '/api' })
  .use(auth)
  .use(adminUsers)
  .use(creators)
  .use(performers)
  .use(songTypes)
  .use(songs);

export type App = typeof app;
