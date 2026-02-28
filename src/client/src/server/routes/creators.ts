import { Elysia, t } from 'elysia';
import { createAuthClient } from '../client';
import { authGuard } from '../middleware';

export const creators = new Elysia({ prefix: '/creators' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    const { data } = await createAuthClient(credential).GET('/creators');

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  })
  .get('/:creatorId', async ({ params: { creatorId }, credential }) => {
    const { data } = await createAuthClient(credential).GET('/creators/{creatorId}', {
      params: {
        path: {
          creatorId,
        },
      },
    });

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  }, {
    params: t.Object({
      creatorId: t.String(),
    }),
  })
  .post('/', async ({ body: { name }, credential }) => {
    const { data } = await createAuthClient(credential).POST('/creators', {
      body: {
        name,
      },
    });

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  }, {
    body: t.Object({
      name: t.String(),
    }),
  })
  .put('/:creatorId', async ({ params: { creatorId }, body: { name }, credential }) => {
    const { data } = await createAuthClient(credential).PUT('/creators/{creatorId}', {
      params: {
        path: {
          creatorId,
        },
      },
      body: {
        name,
      },
    });

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  }, {
    params: t.Object({
      creatorId: t.String(),
    }),
    body: t.Object({
      name: t.String(),
    }),
  })
  .delete('/:creatorId', async ({ params: { creatorId }, credential }) => {
    const { data } = await createAuthClient(credential).DELETE('/creators/{creatorId}', {
      params: {
        path: {
          creatorId,
        },
      },
    });

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  }, {
    params: t.Object({
      creatorId: t.String(),
    }),
  });
