import { Elysia, t } from 'elysia';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const SongTypeValueSchema = t.Union([
  t.Literal(1),
  t.Literal(2),
  t.Literal(3),
  t.Literal(4),
  t.Literal(5),
  t.Literal(6),
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
  .post('/', async ({ body: { title, description, songTypeValue, arrangers, composers, lyricists }, credential }) => {
    return resolveApiResponse(
      await createAuthClient(credential).POST('/songs', {
        body: {
          title,
          description,
          songTypeValue,
          arrangers,
          composers,
          lyricists,
        },
      }),
    );
  }, {
    body: t.Object({
      title: t.String(),
      description: t.String(),
      songTypeValue: SongTypeValueSchema,
      arrangers: CreatorRefSchema,
      composers: CreatorRefSchema,
      lyricists: CreatorRefSchema,
    }),
  })
  .put('/:songId', async ({ params: { songId }, body: { title, description, songTypeValue, orderNo, arrangers, composers, lyricists }, credential }) => {
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
          orderNo,
          arrangers,
          composers,
          lyricists,
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
      orderNo: t.Number(),
      arrangers: CreatorRefSchema,
      composers: CreatorRefSchema,
      lyricists: CreatorRefSchema,
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
