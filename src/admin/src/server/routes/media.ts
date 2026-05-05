import { Elysia, t } from 'elysia';
import { mediaServiceCreateMedia, mediaServiceSearchMedia } from '../../generated';
import type { MediaTypeValue, PerPage } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const MediaTypeValueSchema = t.Union([t.Literal(1), t.Literal(2), t.Literal(3), t.Literal(4), t.Literal(99)]);

export const media = new Elysia({ prefix: '/media' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await mediaServiceSearchMedia({
          client,
          query: {
            title: query.title || undefined,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
          },
        }));
      });
    },
    {
      query: t.Object({
        title: t.Optional(t.String()),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { title, url, typeValue, isDisplay }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await mediaServiceCreateMedia({
          client,
          body: {
            title,
            url,
            typeValue: typeValue as MediaTypeValue,
            isDisplay,
          },
        }));
      });
    },
    {
      body: t.Object({
        title: t.String(),
        url: t.String(),
        typeValue: MediaTypeValueSchema,
        isDisplay: t.Boolean(),
      }),
    },
  );
