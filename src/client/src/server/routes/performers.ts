import { Elysia, t } from 'elysia';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const performers = new Elysia({ prefix: '/performers' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).GET('/performers'),
    );
  })
  .get('/:performerId', async ({ params: { performerId }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).GET('/performers/{performerId}', {
        params: {
          path: {
            performerId,
          },
        },
      }),
    );
  }, {
    params: t.Object({
      performerId: t.String(),
    }),
  })
  .post('/', async ({ body: { name }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).POST('/performers', {
        body: {
          name,
        },
      }),
    );
  }, {
    body: t.Object({
      name: t.String(),
    }),
  })
  .put('/:performerId', async ({ params: { performerId }, body: { name, orderNo }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).PUT('/performers/{performerId}', {
        params: {
          path: {
            performerId,
          },
        },
        body: {
          name,
          orderNo,
        },
      }),
    );
  }, {
    params: t.Object({
      performerId: t.String(),
    }),
    body: t.Object({
      name: t.String(),
      orderNo: t.Number(),
    }),
  })
  .delete('/:performerId', async ({ params: { performerId }, credential }) => {
    resolveApiResponse(
      await createAuthClient(credential).DELETE('/performers/{performerId}', {
        params: {
          path: {
            performerId,
          },
        },
      }),
    );
  }, {
    params: t.Object({
      performerId: t.String(),
    }),
  });
