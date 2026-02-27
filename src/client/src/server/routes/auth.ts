import { randomBytes } from 'node:crypto';
import { Elysia, t } from 'elysia';
import { apiClient } from '../client';
import { SESSION_TTL_SECONDS } from '../constants';
import { redis } from '../redis';

const generateRandomBytes = (): string => {
  return randomBytes(32).toString('base64url');
};

export const auth = new Elysia({ prefix: '/auth' })
  .post('/login', async ({ body: { email, password }, cookie: { session, csrf } }) => {
    const { data } = await apiClient.POST('/auth/login', {
      body: {
        email,
        password,
      },
    });

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    const sessionId = generateRandomBytes();
    const csrfToken = generateRandomBytes();

    await redis.set(
      `session:${sessionId}`,
      { ...data, csrfToken },
      { ex: SESSION_TTL_SECONDS },
    );

    await session?.set({
      value: sessionId,
      httpOnly: true,
      secure: true,
      sameSite: 'strict',
      path: '/',
      maxAge: SESSION_TTL_SECONDS,
    });

    await csrf?.set({
      value: csrfToken,
      httpOnly: false,
      secure: true,
      sameSite: 'strict',
      path: '/',
      maxAge: SESSION_TTL_SECONDS,
    });
  }, {
    body: t.Object({
      email: t.String(),
      password: t.String(),
    }),
  });
