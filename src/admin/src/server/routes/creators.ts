import { Elysia, t } from 'elysia';
import { creatorServiceCreateCreator, creatorServiceDeleteCreator, creatorServiceGetCreator, creatorServiceSearchCreators, creatorServiceUpdateCreator } from '../../generated';
import type { CreatorSearchSortBy, PerPage, SortOrder } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const creators = new Elysia({ prefix: '/creators' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, credential }) => {
      return resolveApiResponse(
        await creatorServiceSearchCreators({
          client: createAuthClient(credential),
          query: {
            name: query.name || undefined,
            sort: (query.sort ?? 'order_no') as CreatorSearchSortBy,
            order: (query.order ?? 'asc') as SortOrder,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
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
      return resolveApiResponse(await creatorServiceGetCreator({ client: createAuthClient(credential), path: { creatorId } }));
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
      return resolveApiResponse(await creatorServiceCreateCreator({ client: createAuthClient(credential), body: { name } }));
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
      return resolveApiResponse(await creatorServiceUpdateCreator({ client: createAuthClient(credential), path: { creatorId }, body: { name, orderNo } }));
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
      resolveApiResponse(await creatorServiceDeleteCreator({ client: createAuthClient(credential), path: { creatorId } }));
    },
    {
      params: t.Object({
        creatorId: t.String(),
      }),
    },
  );
