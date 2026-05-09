import { Elysia, t } from 'elysia';
import { releaseServiceSearchReleases } from '../../generated';
import type { PerPage, ReleaseDistributionTypeValue, ReleaseTypeValue } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const releases = new Elysia({ prefix: '/releases' })
  .use(authGuard)
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
  );
