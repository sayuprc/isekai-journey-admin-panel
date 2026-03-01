import { Elysia, t } from 'elysia';
import { ApiError } from './errors';
import { redis } from './redis';
import type { Credential } from './types';

export const authGuard = new Elysia({ name: 'authGuard' })
  .guard({
    headers: t.Object({
      // treaty で必ず x-csrf-token を取ってくるようにしているが、CSR なところで明示的に書かないと波線が出るのでいったん optional にしている
      'x-csrf-token': t.Optional(t.String()),
      // CSRF では自動送信されるため、tsx では指定してないが波線が出るのでいったん optional にしている
      'cookie': t.Optional(t.String()),
    }),
  })
  .resolve(async ({ headers, cookie: { session } }) => {
    if (!session?.value) {
      throw new ApiError(401, {});
    }

    if (!headers['x-csrf-token']) {
      throw new ApiError(403, {});
    }

    const credential = await redis.get<Credential>(`session:${session.value}`);

    if (!credential) {
      throw new ApiError(401, {});
    }

    if (credential.csrfToken !== headers['x-csrf-token']) {
      throw new ApiError(403, {});
    }

    return { credential };
  })
  .as('scoped');
