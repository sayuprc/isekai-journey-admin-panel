import { Elysia, t } from 'elysia';
import { songTagServiceSearchSongTags, songTagServiceCreateSongTag, songTagServiceListSongTags } from '../../generated';
import type { PerPage, SongTagSearchSortBy, SortOrder } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const songTags = new Elysia({ prefix: '/song-tags' })
  .use(authGuard)
  .get('/', async ({ credential }) => {
    return resolveApiResponse(await songTagServiceListSongTags({ client: createAuthClient(credential) }));
  })
  .get(
    '/search',
    async ({ query, credential }) => {
      return resolveApiResponse(
        await songTagServiceSearchSongTags({
          client: createAuthClient(credential),
          query: {
            name: query.name || undefined,
            sort: (query.sort ?? 'order_no') as SongTagSearchSortBy,
            order: (query.order ?? 'asc') as SortOrder,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 50) as PerPage,
          },
        }),
      );
    },
    {
      query: t.Object({
        name: t.Optional(t.String()),
        sort: t.Optional(t.Union([t.Literal('name'), t.Literal('order_no')])),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name }, credential }) => {
      return resolveApiResponse(await songTagServiceCreateSongTag({ client: createAuthClient(credential), body: { name } }));
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  );
