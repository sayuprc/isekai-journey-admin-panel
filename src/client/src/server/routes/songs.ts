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
  .get('/', async ({ credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).GET('/songs'),
    );
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
  .post('/', async ({ body: { title, description, songTypeValue, attributeValue, lyricists, composers, arrangers }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).POST('/songs', {
        body: {
          title,
          description,
          songTypeValue,
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
      songTypeValue: SongTypeValueSchema,
      attributeValue: t.Optional(SongAttributeValueSchema),
      lyricists: CreatorRefSchema,
      composers: CreatorRefSchema,
      arrangers: CreatorRefSchema,
    }),
  })
  .put('/:songId', async ({ params: { songId }, body: { title, description, songTypeValue, attributeValue, orderNo, composers, lyricists, arrangers }, credential }) => {
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
          songTypeValue,
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
      songTypeValue: SongTypeValueSchema,
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
