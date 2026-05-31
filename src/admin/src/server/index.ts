import { Elysia } from 'elysia';
import { ApiError } from './errors';
import { adminUsers } from './routes/admin-users';
import { auditLogs } from './routes/audit-logs';
import { auth } from './routes/auth';
import { media } from './routes/media';
import { persons } from './routes/persons';
import { recoveryCodes } from './routes/recovery-codes';
import { releases } from './routes/releases';
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
  .use(auditLogs)
  .use(media)
  .use(persons)
  .use(recoveryCodes)
  .use(releases)
  .use(songTypes)
  .use(songTags)
  .use(songs);

export type App = typeof app;
