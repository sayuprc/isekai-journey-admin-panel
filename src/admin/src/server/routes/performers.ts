import { Elysia, t } from 'elysia';
import { performerServiceCreatePerformer, performerServiceDeletePerformer, performerServiceGetPerformer, performerServiceSearchPerformers, performerServiceUpdatePerformer } from '../../generated';
import type { PerPage, PerformerSearchSortBy, SortOrder } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const performers = new Elysia({ prefix: '/performers' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await performerServiceSearchPerformers({
            client,
            query: {
              name: query.name || undefined,
              sort: (query.sort ?? 'order_no') as PerformerSearchSortBy,
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
    '/:performerId',
    async ({ params: { performerId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await performerServiceGetPerformer({ client, path: { performerId } }));
      });
    },
    {
      params: t.Object({
        performerId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await performerServiceCreatePerformer({ client, body: { name } }));
      });
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  )
  .put(
    '/:performerId',
    async ({ params: { performerId }, body: { name, orderNo }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await performerServiceUpdatePerformer({ client, path: { performerId }, body: { name, orderNo } }));
      });
    },
    {
      params: t.Object({
        performerId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        orderNo: t.Number(),
      }),
    },
  )
  .delete(
    '/:performerId',
    async ({ params: { performerId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        resolveApiResponse(await performerServiceDeletePerformer({ client, path: { performerId } }));
      });
    },
    {
      params: t.Object({
        performerId: t.String(),
      }),
    },
  );
