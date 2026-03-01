import { Elysia, t } from 'elysia';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const creators = new Elysia({ prefix: '/creators' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).GET('/creators'),
    );
  })
  .get('/:creatorId', async ({ params: { creatorId }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).GET('/creators/{creatorId}', {
        params: {
          path: {
            creatorId,
          },
        },
      }),
    );
  }, {
    params: t.Object({
      creatorId: t.String(),
    }),
  })
  .post('/', async ({ body: { name }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).POST('/creators', {
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
  .put('/:creatorId', async ({ params: { creatorId }, body: { name, orderNo }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).PUT('/creators/{creatorId}', {
        params: {
          path: {
            creatorId,
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
      creatorId: t.String(),
    }),
    body: t.Object({
      name: t.String(),
      orderNo: t.Number(),
    }),
  })
  .delete('/:creatorId', async ({ params: { creatorId }, credential }) => {
    resolveApiResponse(
      await createAuthClient(credential).DELETE('/creators/{creatorId}', {
        params: {
          path: {
            creatorId,
          },
        },
      }),
    );
  }, {
    params: t.Object({
      creatorId: t.String(),
    }),
  });
