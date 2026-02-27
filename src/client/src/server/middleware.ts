import { Elysia, t } from 'elysia';
import { redis } from './redis';
import type { Credential } from './types';

/**
 * 認証ガード付きの Elysia インスタンスを生成する。
 *
 * セッション Cookie と CSRF トークンを検証し、
 * resolve で credential をダウンストリームのハンドラに渡す。
 */
export const withAuth = (prefix: string) => {
  return new Elysia({ prefix })
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
        throw new Error('Unauthorized: session cookie がありません');
      }

      if (!headers['x-csrf-token']) {
        throw new Error('Forbidden: CSRF トークンがありません');
      }

      const credential = await redis.get<Credential>(`session:${session.value}`);

      if (!credential) {
        throw new Error('Unauthorized: セッションが無効です');
      }

      if (credential.csrfToken !== headers['x-csrf-token']) {
        throw new Error('Forbidden: CSRF トークンが一致しません');
      }

      return { credential };
    });
};
