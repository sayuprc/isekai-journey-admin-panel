import { createAuthClient } from '../client';
import { withAuth } from '../middleware';

export const adminUsers = withAuth('/admin-users')
  .get('/', async ({ credential }) => {
    const { data } = await createAuthClient(credential).GET('/admin-users');

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  });
