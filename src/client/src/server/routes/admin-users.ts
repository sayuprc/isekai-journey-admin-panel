import { Elysia } from 'elysia';
import { createAuthClient } from '../client';
import { authGuard } from '../middleware';

export const adminUsers = new Elysia({ prefix: '/admin-users' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    const { data } = await createAuthClient(credential).GET('/admin-users');

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  });
