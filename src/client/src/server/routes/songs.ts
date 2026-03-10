import { Elysia, t } from 'elysia';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const SongTypeValueSchema = t.Union([
  t.Literal(1),
  t.Literal(2),
]);

const SongAttributeValueSchema = t.Union([
  t.Literal(1),
  t.Literal(2),
  t.Literal(3),
  t.Literal(4),
  t.Literal(5),
]);

const CreatorRefSchema = t.Array(
  t.Object({ creatorId: t.String() }),
);

export const songs = new Elysia({ prefix: '/songs' })
  .use(authGuard)
  .get('/search', async ({ query, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).GET('/songs/search', {
        params: {
          query: {
            title: query.title || undefined,
            type: query.type,
            attribute: query.attribute,
            sort: query.sort ?? 'order_no',
            order: query.order ?? 'asc',
            page: query.page ?? 1,
            per_page: query.per_page ?? 25,
          },
        },
      }),
    );
  }, {
    query: t.Object({
      title: t.String(),
      type: t.Optional(t.Number()),
      attribute: t.Optional(t.Number()),
      sort: t.Optional(t.Union([t.Literal('title'), t.Literal('order_no')])),
      order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
      page: t.Optional(t.Number()),
      per_page: t.Optional(t.Number()),
    }),
  })
  .get('/:songId', async ({ params: { songId }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).GET('/songs/{songId}', {
        params: {
          path: {
            songId,
          },
        },
      }),
    );
  }, {
    params: t.Object({
      songId: t.String(),
    }),
  })
  .post('/', async ({ body: { title, description, typeValue, attributeValue, lyricists, composers, arrangers }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).POST('/songs', {
        body: {
          title,
          description,
          typeValue,
          attributeValue,
          lyricists,
          composers,
          arrangers,
        },
      }),
    );
  }, {
    body: t.Object({
      title: t.String(),
      description: t.String(),
      typeValue: SongTypeValueSchema,
      attributeValue: t.Optional(SongAttributeValueSchema),
      lyricists: CreatorRefSchema,
      composers: CreatorRefSchema,
      arrangers: CreatorRefSchema,
    }),
  })
  .put('/:songId', async ({ params: { songId }, body: { title, description, typeValue, attributeValue, orderNo, composers, lyricists, arrangers }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).PUT('/songs/{songId}', {
        params: {
          path: {
            songId: songId,
          },
        },
        body: {
          title,
          description,
          typeValue,
          attributeValue,
          orderNo,
          lyricists,
          composers,
          arrangers,
        },
      }),
    );
  }, {
    params: t.Object({
      songId: t.String(),
    }),
    body: t.Object({
      title: t.String(),
      description: t.String(),
      typeValue: SongTypeValueSchema,
      attributeValue: t.Optional(SongAttributeValueSchema),
      orderNo: t.Number(),
      lyricists: CreatorRefSchema,
      composers: CreatorRefSchema,
      arrangers: CreatorRefSchema,
    }),
  })
  .delete('/:songId', async ({ params: { songId }, credential }) => {
    resolveApiResponse(
      await createAuthClient(credential).DELETE('/songs/{songId}', {
        params: {
          path: {
            songId,
          },
        },
      }),
    );
  }, {
    params: t.Object({
      songId: t.String(),
    }),
  });
