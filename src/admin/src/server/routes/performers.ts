import { Elysia, t } from 'elysia';
import { performerServiceCreatePerformer, performerServiceDeletePerformer, performerServiceGetPerformer, performerServiceSearchPerformers, performerServiceUpdatePerformer } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const performers = new Elysia({ prefix: '/performers' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, credential }) => {
      return resolveApiResponse(
        await performerServiceSearchPerformers({
          client: createAuthClient(credential),
          query: {
            name: query.name || undefined,
            sort: query.sort ?? 'order_no',
            order: query.order ?? 'asc',
            page: query.page ?? 1,
            per_page: query.per_page ?? 25,
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
    '/:performerId',
    async ({ params: { performerId }, credential }) => {
      return resolveApiResponse(await performerServiceGetPerformer({ client: createAuthClient(credential), path: { performerId } }));
    },
    {
      params: t.Object({
        performerId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name }, credential }) => {
      return resolveApiResponse(await performerServiceCreatePerformer({ client: createAuthClient(credential), body: { name } }));
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  )
  .put(
    '/:performerId',
    async ({ params: { performerId }, body: { name, orderNo }, credential }) => {
      return resolveApiResponse(await performerServiceUpdatePerformer({ client: createAuthClient(credential), path: { performerId }, body: { name, orderNo } }));
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
    async ({ params: { performerId }, credential }) => {
      resolveApiResponse(await performerServiceDeletePerformer({ client: createAuthClient(credential), path: { performerId } }));
    },
    {
      params: t.Object({
        performerId: t.String(),
      }),
    },
  );
