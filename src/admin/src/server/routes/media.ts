import { Elysia, t } from 'elysia';
import {
  mediaServiceCreateMedia,
  mediaServiceDeleteMedia,
  mediaServiceGetMedia,
  mediaServiceSearchMedia,
  mediaServiceUpdateMedia,
} from '../../generated';
import type { MediaFormatValue, MediaTypeValue, PerPage } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const MediaTypeValueSchema = t.Union([t.Literal(1), t.Literal(2), t.Literal(3), t.Literal(4), t.Literal(99)]);
const MediaFormatValueSchema = t.Union([
  t.Literal(1),
  t.Literal(2),
  t.Literal(3),
  t.Literal(4),
  t.Literal(5),
  t.Literal(99),
]);

export const media = new Elysia({ prefix: '/media' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async client => {
        return resolveApiResponse(
          await mediaServiceSearchMedia({
            client,
            query: {
              title: query.title || undefined,
              type: query.type as MediaTypeValue | undefined,
              format: query.format as MediaFormatValue | undefined,
              is_display: query.is_display,
              page: query.page ?? 1,
              per_page: (query.per_page ?? 25) as PerPage,
            },
          }),
        );
      });
    },
    {
      query: t.Object({
        title: t.Optional(t.String()),
        type: t.Optional(t.Union([t.Literal('1'), t.Literal('2'), t.Literal('3'), t.Literal('4'), t.Literal('99')])),
        format: t.Optional(
          t.Union([t.Literal('1'), t.Literal('2'), t.Literal('3'), t.Literal('4'), t.Literal('5'), t.Literal('99')]),
        ),
        is_display: t.Optional(t.Boolean()),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:mediaId',
    async ({ params: { mediaId }, authSession }) => {
      return withAuthRetry(authSession, async client => {
        return resolveApiResponse(await mediaServiceGetMedia({ client, path: { mediaId } }));
      });
    },
    {
      params: t.Object({
        mediaId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { title, url, publishedAt, typeValue, formatValue, isDisplay }, authSession }) => {
      return withAuthRetry(authSession, async client => {
        return resolveApiResponse(
          await mediaServiceCreateMedia({
            client,
            body: {
              title,
              url,
              publishedAt,
              typeValue: typeValue as MediaTypeValue,
              formatValue: formatValue as MediaFormatValue,
              isDisplay,
            },
          }),
        );
      });
    },
    {
      body: t.Object({
        title: t.String(),
        url: t.String(),
        publishedAt: t.String(),
        typeValue: MediaTypeValueSchema,
        formatValue: MediaFormatValueSchema,
        isDisplay: t.Boolean(),
      }),
    },
  )
  .put(
    '/:mediaId',
    async ({
      params: { mediaId },
      body: { title, url, publishedAt, typeValue, formatValue, isDisplay },
      authSession,
    }) => {
      return withAuthRetry(authSession, async client => {
        return resolveApiResponse(
          await mediaServiceUpdateMedia({
            client,
            path: { mediaId },
            body: {
              title,
              url,
              publishedAt,
              typeValue: typeValue as MediaTypeValue,
              formatValue: formatValue as MediaFormatValue,
              isDisplay,
            },
          }),
        );
      });
    },
    {
      params: t.Object({
        mediaId: t.String(),
      }),
      body: t.Object({
        title: t.String(),
        url: t.String(),
        publishedAt: t.String(),
        typeValue: MediaTypeValueSchema,
        formatValue: MediaFormatValueSchema,
        isDisplay: t.Boolean(),
      }),
    },
  )
  .delete(
    '/:mediaId',
    async ({ params: { mediaId }, authSession }) => {
      return withAuthRetry(authSession, async client => {
        return resolveApiResponse(
          await mediaServiceDeleteMedia({
            client,
            path: { mediaId },
          }),
        );
      });
    },
    {
      params: t.Object({
        mediaId: t.String(),
      }),
    },
  );
