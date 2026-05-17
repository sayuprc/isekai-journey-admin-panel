import { randomBytes } from 'node:crypto';
import { Elysia, t } from 'elysia';
import { authenticateServiceLogin, authenticateServiceRegister } from '../../generated';
import { client } from '../client';
import { SESSION_TTL_SECONDS } from '../constants';
import { resolveApiResponse } from '../errors';
import { storeSessionCredential } from '../session';

const generateRandomBytes = (): string => {
  return randomBytes(32).toString('base64url');
};

export const auth = new Elysia({ prefix: '/auth' })
  .post(
    '/login',
    async ({ body: { email, password }, cookie: { session, csrf } }) => {
      const data = resolveApiResponse(await authenticateServiceLogin({ client: client, body: { email, password } }));

      const sessionId = generateRandomBytes();
      const csrfToken = generateRandomBytes();

      await storeSessionCredential(sessionId, { ...data, csrfToken });

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
  )
  .post(
    '/register',
    async ({ body: { token, name, email, password } }) => {
      return resolveApiResponse(
        await authenticateServiceRegister({
          client,
          body: { token, name, email, password },
        }),
      );
    },
    {
      body: t.Object({
        token: t.String(),
        name: t.String(),
        email: t.String(),
        password: t.String(),
      }),
    },
  );
