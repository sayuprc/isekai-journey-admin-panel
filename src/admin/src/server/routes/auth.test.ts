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

const loginCalls: unknown[] = [];
const registerCalls: unknown[] = [];

mock.module('../../generated', () => {
  return {
    authenticateServiceRefresh: async () => ({
      data: null,
      response: new Response(null, { status: 200 }),
    }),
    authenticateServiceLogin: async ({ body }: { body: unknown }) => {
      loginCalls.push(body);
      return {
        data: {
          accessToken: 'access-token',
          refreshTokenId: 'refresh-token-id',
          refreshToken: 'refresh-token',
        },
        response: new Response(null, { status: 200 }),
      };
    },
    authenticateServiceRegister: async ({ body }: { body: unknown }) => {
      registerCalls.push(body);

      const payload = body as { token: string };
      if (payload.token === 'invalid-token') {
        return {
          error: { message: 'トークンが不正です' },
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

describe('POST /auth/register', () => {
  beforeEach(() => {
    for (const key of Object.keys(credentials)) {
      delete credentials[key];
    }
    loginCalls.length = 0;
    registerCalls.length = 0;
  });

  it('成功時にセッション/CSRF Cookie を発行し、Redis にクレデンシャルを保存する', async () => {
    const response = await auth.handle(
      new Request('http://localhost/auth/register', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          token: 'plain-token',
          email: 'invitee@example.com',
          name: '新規ユーザー',
          password: 'password',
        }),
      }),
    );

    expect(response.status).toBe(200);
    expect(registerCalls).toHaveLength(1);
    expect(registerCalls[0]).toEqual({
      token: 'plain-token',
      email: 'invitee@example.com',
      name: '新規ユーザー',
      password: 'password',
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
      new Request('http://localhost/auth/register', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({
          token: 'invalid-token',
          email: 'invitee@example.com',
          name: '新規ユーザー',
          password: 'password',
        }),
      }),
    );

    expect(response.status).toBe(400);
    expect(Object.keys(credentials)).toHaveLength(0);
  });
});
