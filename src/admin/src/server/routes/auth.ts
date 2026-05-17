import { randomBytes } from 'node:crypto';
import { Elysia, t } from 'elysia';
import {
  authenticateServiceLogin,
  authenticateServiceRegister,
  type LoginRequest,
  type LoginResponse,
  type RegisterRequest,
} from '../../generated';
import { client } from '../client';
import { SESSION_TTL_SECONDS } from '../constants';
import { resolveApiResponse } from '../errors';
import type { Credential } from '../types';

const generateRandomBytes = (): string => {
  return randomBytes(32).toString('base64url');
};

type CookieOptions = {
  value: string;
  httpOnly: boolean;
  secure: boolean;
  sameSite: 'strict';
  path: '/';
  maxAge: number;
};

type CookieSetter = {
  set: (value: CookieOptions) => Promise<void> | void;
};

type AuthDeps = {
  authenticateLogin: (body: LoginRequest) => Promise<LoginResponse>;
  authenticateRegister: (body: RegisterRequest) => Promise<void>;
  generateToken: () => string;
  storeSessionCredential: (sessionId: string, credential: Credential) => Promise<void>;
};

type LoginContext = {
  body: LoginRequest;
  cookie: {
    session?: CookieSetter;
    csrf?: CookieSetter;
  };
};

type RegisterContext = {
  body: RegisterRequest;
};

const createCookieOptions = (value: string, httpOnly: boolean): CookieOptions => ({
  value,
  httpOnly,
  secure: true,
  sameSite: 'strict',
  path: '/',
  maxAge: SESSION_TTL_SECONDS,
});

const defaultAuthDeps: AuthDeps = {
  authenticateLogin: async body => resolveApiResponse(await authenticateServiceLogin({ client: client, body })),
  authenticateRegister: async (body) => {
    resolveApiResponse(await authenticateServiceRegister({ client: client, body }));
  },
  generateToken: generateRandomBytes,
  storeSessionCredential: async (sessionId, credential) => {
    const { storeSessionCredential } = await import('../session');

    await storeSessionCredential(sessionId, credential);
  },
};

export const createLoginHandler = (deps: AuthDeps = defaultAuthDeps) => {
  return async ({ body, cookie: { session, csrf } }: LoginContext) => {
    const credential = await deps.authenticateLogin(body);
    const sessionId = deps.generateToken();
    const csrfToken = deps.generateToken();

    await deps.storeSessionCredential(sessionId, { ...credential, csrfToken });

    await session?.set(createCookieOptions(sessionId, true));
    await csrf?.set(createCookieOptions(csrfToken, false));
  };
};

export const createRegisterHandler = (deps: AuthDeps = defaultAuthDeps) => {
  return async ({ body }: RegisterContext) => {
    await deps.authenticateRegister(body);
  };
};

export const auth = new Elysia({ prefix: '/auth' })
  .post('/login', createLoginHandler(), {
    body: t.Object({
      email: t.String(),
      password: t.String(),
    }),
  })
  .post('/register', createRegisterHandler(), {
    body: t.Object({
      name: t.String(),
      email: t.String(),
      password: t.String(),
      registrationToken: t.String(),
    }),
  });
