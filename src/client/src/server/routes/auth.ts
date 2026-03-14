import { randomBytes } from 'node:crypto';
import { Elysia, t } from 'elysia';
import { client } from '../client';
import { SESSION_TTL_SECONDS } from '../constants';
import { resolveApiResponse } from '../errors';
import { redis } from '../redis';

const generateRandomBytes = (): string => {
  return randomBytes(32).toString('base64url');
};

export const auth = new Elysia({ prefix: '/auth' }).post(
  '/login',
  async ({ body: { email, password }, cookie: { session, csrf } }) => {
    const data = resolveApiResponse(
      await client.POST('/auth/login', {
        body: {
          email,
          password,
        },
      }),
    );

    const sessionId = generateRandomBytes();
    const csrfToken = generateRandomBytes();

    await redis.set(`session:${sessionId}`, { ...data, csrfToken }, { ex: SESSION_TTL_SECONDS });

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
  },
  {
    body: t.Object({
      email: t.String(),
      password: t.String(),
    }),
  },
);
