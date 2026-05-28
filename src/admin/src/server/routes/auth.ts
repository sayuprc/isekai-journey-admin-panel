import { randomBytes } from 'node:crypto';
import { Elysia, t } from 'elysia';
import {
  authenticateServiceLoginFinish,
  authenticateServiceLoginStart,
  authenticateServiceRegisterFinish,
  authenticateServiceRegisterStart,
} from '../../generated';
import type { LoginFinishRequest, RegisterFinishRequest } from '../../generated';
import { client } from '../client';
import { SESSION_TTL_SECONDS } from '../constants';
import { resolveApiResponse } from '../errors';
import { storeSessionCredential } from '../session';

const generateRandomBytes = (): string => {
  return randomBytes(32).toString('base64url');
};

const webAuthnCredentialSchema = t.Record(t.String(), t.Any());

const setAuthCookies = async (
  session: { set: (value: Record<string, unknown>) => Promise<unknown> | unknown } | undefined,
  csrf: { set: (value: Record<string, unknown>) => Promise<unknown> | unknown } | undefined,
  sessionId: string,
  csrfToken: string,
): Promise<void> => {
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
};

export const auth = new Elysia({ prefix: '/auth' })
  .post(
    '/login/start',
    async ({ body: { email } }) => {
      return resolveApiResponse(await authenticateServiceLoginStart({ client: client, body: { email } }));
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
      const body = { authCeremonyId, credential } satisfies LoginFinishRequest;
      const data = resolveApiResponse(
        await authenticateServiceLoginFinish({ client: client, body: body }),
      );

      const sessionId = generateRandomBytes();
      const csrfToken = generateRandomBytes();

      await storeSessionCredential(sessionId, { ...data, csrfToken });

      await setAuthCookies(session, csrf, sessionId, csrfToken);
    },
    {
      body: t.Object({
        authCeremonyId: t.String(),
        credential: webAuthnCredentialSchema,
      }),
    },
  )
  .post(
    '/register/start',
    async ({ body: { token, email, name } }) => {
      return resolveApiResponse(
        await authenticateServiceRegisterStart({ client: client, body: { token, email, name } }),
      );
    },
    {
      body: t.Object({
        token: t.String(),
        email: t.String(),
        name: t.String(),
      }),
    },
  )
  .post(
    '/register/finish',
    async ({ body: { authCeremonyId, token, credential }, cookie: { session, csrf } }) => {
      const body = { authCeremonyId, token, credential } satisfies RegisterFinishRequest;
      const data = resolveApiResponse(
        await authenticateServiceRegisterFinish({ client: client, body: body }),
      );

      const sessionId = generateRandomBytes();
      const csrfToken = generateRandomBytes();

      await storeSessionCredential(sessionId, { ...data, csrfToken });

      await setAuthCookies(session, csrf, sessionId, csrfToken);
    },
    {
      body: t.Object({
        authCeremonyId: t.String(),
        token: t.String(),
        credential: webAuthnCredentialSchema,
      }),
    },
  );
