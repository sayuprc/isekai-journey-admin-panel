import { describe, expect, it } from 'bun:test';
import { SESSION_TTL_SECONDS } from '../constants';
import { createLoginHandler, createRegisterHandler } from './auth';

describe('createLoginHandler', () => {
  it('ログイン成功時は session と csrf の Cookie を発行して認証情報を保存する', async () => {
    const storedCredentials: Array<{ sessionId: string; credential: { accessToken: string; refreshTokenId: string; refreshToken: string; csrfToken: string } }> = [];
    const sessionCookies: Array<Record<string, unknown>> = [];
    const csrfCookies: Array<Record<string, unknown>> = [];
    const generatedTokens = ['session-id', 'csrf-token'];

    const handleLogin = createLoginHandler({
      authenticateLogin: async (body) => {
        expect(body).toEqual({
          email: 'admin@example.com',
          password: 'password',
        });

        return {
          accessToken: 'access-token',
          refreshTokenId: 'refresh-token-id',
          refreshToken: 'refresh-token',
        };
      },
      authenticateRegister: async () => {
        throw new Error('unexpected');
      },
      generateToken: () => {
        const token = generatedTokens.shift();

        if (!token) {
          throw new Error('token exhausted');
        }

        return token;
      },
      storeSessionCredential: async (sessionId, credential) => {
        storedCredentials.push({ sessionId, credential });
      },
    });

    await handleLogin({
      body: {
        email: 'admin@example.com',
        password: 'password',
      },
      cookie: {
        session: {
          set: async (value) => {
            sessionCookies.push(value);
          },
        },
        csrf: {
          set: async (value) => {
            csrfCookies.push(value);
          },
        },
      },
    });

    expect(storedCredentials).toEqual([
      {
        sessionId: 'session-id',
        credential: {
          accessToken: 'access-token',
          refreshTokenId: 'refresh-token-id',
          refreshToken: 'refresh-token',
          csrfToken: 'csrf-token',
        },
      },
    ]);
    expect(sessionCookies).toEqual([
      {
        value: 'session-id',
        httpOnly: true,
        secure: true,
        sameSite: 'strict',
        path: '/',
        maxAge: SESSION_TTL_SECONDS,
      },
    ]);
    expect(csrfCookies).toEqual([
      {
        value: 'csrf-token',
        httpOnly: false,
        secure: true,
        sameSite: 'strict',
        path: '/',
        maxAge: SESSION_TTL_SECONDS,
      },
    ]);
  });
});

describe('createRegisterHandler', () => {
  it('register は registrationToken を upstream に渡し Cookie を発行しない', async () => {
    const requestBodies: Array<{ name: string; email: string; password: string; registrationToken: string }> = [];
    const storedCredentials: string[] = [];
    const generatedTokens: string[] = [];

    const handleRegister = createRegisterHandler({
      authenticateLogin: async () => {
        throw new Error('unexpected');
      },
      authenticateRegister: async (body) => {
        requestBodies.push(body);
      },
      generateToken: () => {
        generatedTokens.push('called');
        return 'unexpected-token';
      },
      storeSessionCredential: async () => {
        storedCredentials.push('called');
      },
    });

    await handleRegister({
      body: {
        name: 'Admin User',
        email: 'admin@example.com',
        password: 'password',
        registrationToken: 'plain-registration-token',
      },
    });

    expect(requestBodies).toEqual([
      {
        name: 'Admin User',
        email: 'admin@example.com',
        password: 'password',
        registrationToken: 'plain-registration-token',
      },
    ]);
    expect(storedCredentials).toEqual([]);
    expect(generatedTokens).toEqual([]);
  });
});
