import { t } from 'elysia';
import { createAuthClient } from '../client';
import { withAuth } from '../middleware';

export const performers = withAuth('/performers')
  .get('/', async ({ credential }) => {
    const { data } = await createAuthClient(credential).GET('/performers');

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  })
  .get('/:performerId', async ({ params: { performerId }, credential }) => {
    const { data } = await createAuthClient(credential).GET('/performers/{performerId}', {
      params: {
        path: {
          performerId,
        },
      },
    });

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  }, {
    params: t.Object({
      performerId: t.String(),
    }),
  })
  .post('/', async ({ body: { name }, credential }) => {
    const { data } = await createAuthClient(credential).POST('/performers', {
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
  .put('/:performerId', async ({ params: { performerId }, body: { name, orderNo }, credential }) => {
    const { data } = await createAuthClient(credential).PUT('/performers/{performerId}', {
      params: {
        path: {
          performerId,
        },
      },
      body: {
        name,
        orderNo,
      },
    });

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
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
    const { data } = await createAuthClient(credential).DELETE('/performers/{performerId}', {
      params: {
        path: {
          performerId,
        },
      },
    });

    if (!data) {
      throw new Error('TODO エラーハンドリング');
    }

    return data;
  }, {
    params: t.Object({
      performerId: t.String(),
    }),
  });
