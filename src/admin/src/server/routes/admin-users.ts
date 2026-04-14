import Elysia from 'elysia';
import { adminUserServiceListAdminUsers } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const adminUsers = new Elysia({ prefix: '/admin-users' }).use(authGuard).get('/', async ({ credential }) => {
  return resolveApiResponse(await adminUserServiceListAdminUsers({ client: createAuthClient(credential) }));
});
