import { beforeEach, describe, expect, it, mock } from 'bun:test';

const credentials: Record<string, unknown> = {};

mock.module('@upstash/redis', () => {
  class Redis {
    async set(key: string, value: unknown): Promise<'OK'> {
      credentials[key] = value;
      return 'OK';
    }

    async get<T>(key: string): Promise<T | null> {
      return (credentials[key] as T | undefined) ?? null;
    }

    async del(key: string): Promise<number> {
      delete credentials[key];
      return 1;
    }
  }
  return { Redis };
});

const loginStartCalls: unknown[] = [];
const loginFinishCalls: unknown[] = [];
const registerStartCalls: unknown[] = [];
const registerFinishCalls: unknown[] = [];

mock.module('../../generated', () => {
  return {
    authenticateServiceRefresh: async () => ({
      data: null,
      response: new Response(null, { status: 200 }),
    }),
    authenticateServiceLoginStart: async ({ body }: { body: unknown }) => {
      loginStartCalls.push(body);
      return {
        data: {
          authCeremonyId: 'login-auth-ceremony-id',
          publicKey: { challenge: 'login-challenge' },
        },
        response: new Response(null, { status: 200 }),
      };
    },
    authenticateServiceLoginFinish: async ({ body }: { body: unknown }) => {
      loginFinishCalls.push(body);

      const payload = body as { credential: { fail?: boolean } };
      if (payload.credential.fail) {
        return {
          error: { message: 'credential が不正です' },
          response: new Response(null, { status: 401 }),
        };
      }

      return {
        data: {
          accessToken: 'access-token',
          refreshTokenId: 'refresh-token-id',
          refreshToken: 'refresh-token',
        },
        response: new Response(null, { status: 200 }),
      };
    },
    authenticateServiceRegisterStart: async ({ body }: { body: unknown }) => {
      registerStartCalls.push(body);

      const payload = body as { token: string };
      if (payload.token === 'invalid-token') {
        return {
          error: { message: 'トークンが不正です' },
          response: new Response(null, { status: 400 }),
        };
      }

      return {
        data: {
          authCeremonyId: 'auth-ceremony-id',
          publicKey: { challenge: 'challenge' },
        },
        response: new Response(null, { status: 200 }),
      };
    },
    authenticateServiceRegisterFinish: async ({ body }: { body: unknown }) => {
      registerFinishCalls.push(body);

      const payload = body as { credential: { fail?: boolean } };
      if (payload.credential.fail) {
        return {
          error: { message: 'credential が不正です' },
          response: new Response(null, { status: 400 }),
        };
      }

      return {
        data: {
          accessToken: 'register-access-token',
          refreshTokenId: 'register-refresh-token-id',
          refreshToken: 'register-refresh-token',
        },
        response: new Response(null, { status: 200 }),
      };
    },
  };
});

const { auth } = await import('./auth');

describe('POST /auth/login/start', () => {
  beforeEach(() => {
    for (const key of Object.keys(credentials)) {
      delete credentials[key];
    }
    loginStartCalls.length = 0;
    loginFinishCalls.length = 0;
    registerStartCalls.length = 0;
    registerFinishCalls.length = 0;
  });

  it('email だけでログイン開始 API を呼ぶ', async () => {
    const response = await auth.handle(
      new Request('http://localhost/auth/login/start', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          email: 'user@example.com',
        }),
      }),
    );

    expect(response.status).toBe(200);
    expect(await response.json()).toEqual({
      authCeremonyId: 'login-auth-ceremony-id',
      publicKey: { challenge: 'login-challenge' },
    });
    expect(loginStartCalls).toHaveLength(1);
    expect(loginStartCalls[0]).toEqual({
      email: 'user@example.com',
    });
    expect(response.headers.getSetCookie()).toHaveLength(0);
    expect(Object.keys(credentials)).toHaveLength(0);
  });
});

describe('POST /auth/login/finish', () => {
  beforeEach(() => {
    for (const key of Object.keys(credentials)) {
      delete credentials[key];
    }
    loginStartCalls.length = 0;
    loginFinishCalls.length = 0;
    registerStartCalls.length = 0;
    registerFinishCalls.length = 0;
  });

  it('ログイン完了 API を呼び、成功時にセッション/CSRF Cookie を発行する', async () => {
    const response = await auth.handle(
      new Request('http://localhost/auth/login/finish', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          authCeremonyId: 'login-auth-ceremony-id',
          credential: { id: 'credential-id' },
        }),
      }),
    );

    expect(response.status).toBe(200);
    expect(loginFinishCalls).toHaveLength(1);
    expect(loginFinishCalls[0]).toEqual({
      authCeremonyId: 'login-auth-ceremony-id',
      credential: { id: 'credential-id' },
    });

    const cookies = response.headers.getSetCookie();
    expect(cookies.some(cookie => cookie.startsWith('session='))).toBe(true);
    expect(cookies.some(cookie => cookie.startsWith('csrf='))).toBe(true);

    const sessionEntries = Object.entries(credentials).filter(([key]) => key.startsWith('session:'));
    expect(sessionEntries).toHaveLength(1);
    const [, stored] = sessionEntries[0]!;
    expect(stored).toMatchObject({
      accessToken: 'access-token',
      refreshTokenId: 'refresh-token-id',
      refreshToken: 'refresh-token',
    });
    expect((stored as { csrfToken: string }).csrfToken).toBeTruthy();
  });

  it('API エラー時はエラーレスポンスをそのまま返す', async () => {
    const response = await auth.handle(
      new Request('http://localhost/auth/login/finish', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          authCeremonyId: 'login-auth-ceremony-id',
          credential: { fail: true },
        }),
      }),
    );

    expect(response.status).toBe(401);
    expect(Object.keys(credentials)).toHaveLength(0);
  });
});

describe('POST /auth/register/start', () => {
  beforeEach(() => {
    for (const key of Object.keys(credentials)) {
      delete credentials[key];
    }
    loginStartCalls.length = 0;
    loginFinishCalls.length = 0;
    registerStartCalls.length = 0;
    registerFinishCalls.length = 0;
  });

  it('password を含めず登録開始 API を呼ぶ', async () => {
    const response = await auth.handle(
      new Request('http://localhost/auth/register/start', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          token: 'plain-token',
          email: 'invitee@example.com',
          name: '新規ユーザー',
        }),
      }),
    );

    expect(response.status).toBe(200);
    expect(registerStartCalls).toHaveLength(1);
    expect(registerStartCalls[0]).toEqual({
      token: 'plain-token',
      email: 'invitee@example.com',
      name: '新規ユーザー',
    });
    expect(response.headers.getSetCookie()).toHaveLength(0);
    expect(Object.keys(credentials)).toHaveLength(0);
  });
});

describe('POST /auth/register/finish', () => {
  beforeEach(() => {
    for (const key of Object.keys(credentials)) {
      delete credentials[key];
    }
    loginStartCalls.length = 0;
    loginFinishCalls.length = 0;
    registerStartCalls.length = 0;
    registerFinishCalls.length = 0;
  });

  it('password を含めず登録完了 API を呼び、成功時にセッション/CSRF Cookie を発行する', async () => {
    const response = await auth.handle(
      new Request('http://localhost/auth/register/finish', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          authCeremonyId: 'auth-ceremony-id',
          credential: { id: 'credential-id' },
        }),
      }),
    );

    expect(response.status).toBe(200);
    expect(registerFinishCalls).toHaveLength(1);
    expect(registerFinishCalls[0]).toEqual({
      authCeremonyId: 'auth-ceremony-id',
      credential: { id: 'credential-id' },
    });

    const cookies = response.headers.getSetCookie();
    expect(cookies.some(cookie => cookie.startsWith('session='))).toBe(true);
    expect(cookies.some(cookie => cookie.startsWith('csrf='))).toBe(true);

    const sessionEntries = Object.entries(credentials).filter(([key]) => key.startsWith('session:'));
    expect(sessionEntries).toHaveLength(1);
    const [, stored] = sessionEntries[0]!;
    expect(stored).toMatchObject({
      accessToken: 'register-access-token',
      refreshTokenId: 'register-refresh-token-id',
      refreshToken: 'register-refresh-token',
    });
    expect((stored as { csrfToken: string }).csrfToken).toBeTruthy();
  });

  it('API エラー時はエラーレスポンスをそのまま返す', async () => {
    const response = await auth.handle(
      new Request('http://localhost/auth/register/finish', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          authCeremonyId: 'auth-ceremony-id',
          credential: { fail: true },
        }),
      }),
    );

    expect(response.status).toBe(400);
    expect(Object.keys(credentials)).toHaveLength(0);
  });
});
