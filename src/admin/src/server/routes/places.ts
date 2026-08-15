import { Elysia, t } from 'elysia';
import {
  placeServiceCreatePlace,
  placeServiceDeletePlace,
  placeServiceGetPlace,
  placeServiceSearchPlaces,
  placeServiceUpdatePlace,
} from '../../generated';
import type { PerPage, PlaceKindValue, PlaceSearchSortBy, SortOrder } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const places = new Elysia({ prefix: '/places' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await placeServiceSearchPlaces({
            client,
            query: {
              name: query.name || undefined,
              kindValue: query.kindValue as PlaceKindValue | undefined,
              sort: (query.sort ?? 'name') as PlaceSearchSortBy,
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
        kindValue: t.Optional(t.Union([t.Literal(1), t.Literal(2)])),
        sort: t.Optional(t.Literal('name')),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:placeId',
    async ({ params: { placeId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await placeServiceGetPlace({ client, path: { placeId } }));
      });
    },
    {
      params: t.Object({
        placeId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name, kindValue }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await placeServiceCreatePlace({ client, body: { name, kindValue: kindValue as PlaceKindValue } }),
        );
      });
    },
    {
      body: t.Object({
        name: t.String(),
        kindValue: t.Number(),
      }),
    },
  )
  .put(
    '/:placeId',
    async ({ params: { placeId }, body: { name, kindValue }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await placeServiceUpdatePlace({
            client,
            path: { placeId },
            body: { name, kindValue: kindValue as PlaceKindValue },
          }),
        );
      });
    },
    {
      params: t.Object({
        placeId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        kindValue: t.Number(),
      }),
    },
  )
  .delete(
    '/:placeId',
    async ({ params: { placeId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        resolveApiResponse(await placeServiceDeletePlace({ client, path: { placeId } }));
      });
    },
    {
      params: t.Object({
        placeId: t.String(),
      }),
    },
  );
