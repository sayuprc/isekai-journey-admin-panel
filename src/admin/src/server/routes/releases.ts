import { Elysia, t } from 'elysia';
import { releaseServiceCreateRelease, releaseServiceDeleteRelease, releaseServiceGetRelease, releaseServiceSearchReleases, releaseServiceUpdateRelease } from '../../generated';
import type { PerPage, ReleaseDistributionTypeValue, ReleaseTypeValue } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const releases = new Elysia({ prefix: '/releases' })
  .use(authGuard)
  .post(
    '/',
    async ({ body, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseServiceCreateRelease({ client, body }));
      });
    },
    {
      body: t.Object({
        title: t.String(),
        typeValue: t.Numeric(),
        distributionTypeValue: t.Numeric(),
        releasedOn: t.String(),
        description: t.Optional(t.String()),
        isDisplay: t.Boolean(),
        trackEntries: t.Array(t.Object({
          songId: t.String(),
          trackNo: t.Number(),
        })),
      }),
    },
  )
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseServiceSearchReleases({
          client,
          query: {
            title: query.title || undefined,
            type: query.type as ReleaseTypeValue | undefined,
            distribution_type: query.distribution_type as ReleaseDistributionTypeValue | undefined,
            is_display: query.is_display,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
          },
        }));
      });
    },
    {
      query: t.Object({
        title: t.Optional(t.String()),
        type: t.Optional(t.Union([t.Literal('1'), t.Literal('2'), t.Literal('3'), t.Literal('99')])),
        distribution_type: t.Optional(t.Union([t.Literal('1'), t.Literal('2'), t.Literal('99')])),
        is_display: t.Optional(t.Boolean()),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:releaseId',
    async ({ params: { releaseId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseServiceGetRelease({ client, path: { releaseId } }));
      });
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
    },
  )
  .put(
    '/:releaseId',
    async ({ params: { releaseId }, body, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseServiceUpdateRelease({
          client,
          path: { releaseId },
          body,
        }));
      });
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
      body: t.Object({
        title: t.String(),
        typeValue: t.Numeric(),
        distributionTypeValue: t.Numeric(),
        releasedOn: t.String(),
        description: t.String(),
        isDisplay: t.Boolean(),
        trackEntries: t.Array(t.Object({
          songId: t.String(),
          trackNo: t.Number(),
        })),
      }),
    },
  )
  .delete(
    '/:releaseId',
    async ({ params: { releaseId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseServiceDeleteRelease({
          client,
          path: { releaseId },
        }));
      });
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
    },
  );
