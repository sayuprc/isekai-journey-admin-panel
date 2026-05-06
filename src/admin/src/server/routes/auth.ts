import { randomBytes } from 'node:crypto';
import { Elysia, t } from 'elysia';
import {
  authenticateServiceLoginFinish,
  authenticateServiceLoginStart,
  authenticateServiceRegisterFinish,
  authenticateServiceRegisterStart,
} from '../../generated';
import { client } from '../client';
import { SESSION_TTL_SECONDS } from '../constants';
import { resolveApiResponse } from '../errors';
import { storeSessionCredential } from '../session';

const generateRandomBytes = (): string => {
  return randomBytes(32).toString('base64url');
};

type MutableCookie = {
  set: (value: {
    value: string;
    httpOnly: boolean;
    secure: boolean;
    sameSite: 'strict';
    path: string;
    maxAge: number;
  }) => unknown;
};

const storeAuthenticatedSession = async (
  credential: { accessToken: string; refreshTokenId: string; refreshToken: string },
  session: MutableCookie | undefined,
  csrf: MutableCookie | undefined,
) => {
  const sessionId = generateRandomBytes();
  const csrfToken = generateRandomBytes();

  await storeSessionCredential(sessionId, { ...credential, csrfToken });

  session?.set({
    value: sessionId,
    httpOnly: true,
    secure: true,
    sameSite: 'strict',
    path: '/',
    maxAge: SESSION_TTL_SECONDS,
  });

  csrf?.set({
    value: csrfToken,
    httpOnly: false,
    secure: true,
    sameSite: 'strict',
    path: '/',
    maxAge: SESSION_TTL_SECONDS,
  });
};

export const auth = new Elysia({ prefix: '/auth' })
  .post(
    '/login/start',
    async ({ body: { email } }) => {
      return resolveApiResponse(await authenticateServiceLoginStart({ client, body: { email } }));
    },
    {
      body: t.Object({
        email: t.String(),
      }),
    },
  )
  .post(
    '/login/finish',
    async ({ body: { authCeremonyId, credential }, cookie: { session, csrf } }) => {
      const data = resolveApiResponse(
        await authenticateServiceLoginFinish({ client, body: { authCeremonyId, credential } }),
      );

      await storeAuthenticatedSession(data, session, csrf);
    },
    {
      body: t.Object({
        authCeremonyId: t.String(),
        credential: t.Any(),
      }),
    },
  )
  .post(
    '/register/start',
    async ({ body: { email, registrationToken } }) => {
      return resolveApiResponse(
        await authenticateServiceRegisterStart({ client, body: { email, registrationToken } }),
      );
    },
    {
      body: t.Object({
        email: t.String(),
        registrationToken: t.String(),
      }),
    },
  )
  .post(
    '/register/finish',
    async ({ body: { authCeremonyId, credential }, cookie: { session, csrf } }) => {
      const data = resolveApiResponse(
        await authenticateServiceRegisterFinish({ client, body: { authCeremonyId, credential } }),
      );

      await storeAuthenticatedSession(data, session, csrf);
    },
    {
      body: t.Object({
        authCeremonyId: t.String(),
        credential: t.Any(),
      }),
    },
  );
