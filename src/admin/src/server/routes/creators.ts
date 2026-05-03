import { Elysia, t } from 'elysia';
import { creatorServiceCreateCreator, creatorServiceDeleteCreator, creatorServiceGetCreator, creatorServiceSearchCreators, creatorServiceUpdateCreator } from '../../generated';
import type { CreatorSearchSortBy, PerPage, SortOrder } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const creators = new Elysia({ prefix: '/creators' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await creatorServiceSearchCreators({
            client,
            query: {
              name: query.name || undefined,
              sort: (query.sort ?? 'order_no') as CreatorSearchSortBy,
              order: (query.order ?? 'asc') as SortOrder,
              page: query.page ?? 1,
              per_page: (query.per_page ?? 25) as PerPage,
            },
          }),
        );
      });
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
    async ({ params: { creatorId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await creatorServiceGetCreator({ client, path: { creatorId } }));
      });
    },
    {
      params: t.Object({
        creatorId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await creatorServiceCreateCreator({ client, body: { name } }));
      });
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  )
  .put(
    '/:creatorId',
    async ({ params: { creatorId }, body: { name, orderNo }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await creatorServiceUpdateCreator({ client, path: { creatorId }, body: { name, orderNo } }));
      });
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
    async ({ params: { creatorId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        resolveApiResponse(await creatorServiceDeleteCreator({ client, path: { creatorId } }));
      });
    },
    {
      params: t.Object({
        creatorId: t.String(),
      }),
    },
  );
