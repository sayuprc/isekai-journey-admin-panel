import { Elysia, t } from 'elysia';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const creators = new Elysia({ prefix: '/creators' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    return resolveApiResponse(await createAuthClient(credential).GET('/creators'));
  })
  .get(
    '/search',
    async ({ query, credential }) => {
      return resolveApiResponse(
        await createAuthClient(credential).GET('/creators/search', {
          params: {
            query: {
              name: query.name || undefined,
              sort: query.sort ?? 'order_no',
              order: query.order ?? 'asc',
              page: query.page ?? 1,
              per_page: query.per_page ?? 25,
            },
          },
        }),
      );
    },
    {
      query: t.Object({
        name: t.String(),
        sort: t.Optional(t.Union([t.Literal('name'), t.Literal('order_no')])),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:creatorId',
    async ({ params: { creatorId }, credential }) => {
      return resolveApiResponse(
        await createAuthClient(credential).GET('/creators/{creatorId}', {
          params: {
            path: {
              creatorId,
            },
          },
        }),
      );
    },
    {
      params: t.Object({
        creatorId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name }, credential }) => {
      return resolveApiResponse(
        await createAuthClient(credential).POST('/creators', {
          body: {
            name,
          },
        }),
      );
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  )
  .put(
    '/:creatorId',
    async ({ params: { creatorId }, body: { name, orderNo }, credential }) => {
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
    },
    {
      params: t.Object({
        creatorId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        orderNo: t.Number(),
      }),
    },
  )
  .delete(
    '/:creatorId',
    async ({ params: { creatorId }, credential }) => {
      resolveApiResponse(
        await createAuthClient(credential).DELETE('/creators/{creatorId}', {
          params: {
            path: {
              creatorId,
            },
          },
        }),
      );
    },
    {
      params: t.Object({
        creatorId: t.String(),
      }),
    },
  );
