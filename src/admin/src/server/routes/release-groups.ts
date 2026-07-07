import { Elysia, t } from 'elysia';
import {
  releaseGroupServiceCreateReleaseGroup,
  releaseGroupServiceDeleteReleaseGroup,
  releaseGroupServiceGetReleaseGroup,
  releaseGroupServiceSearchReleaseGroups,
  releaseGroupServiceUpdateReleaseGroup,
} from '../../generated';
import type { PerPage, ReleaseGroupTypeValue } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const releaseGroups = new Elysia({ prefix: '/release-groups' })
  .use(authGuard)
  .post(
    '/',
    async ({ body, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseGroupServiceCreateReleaseGroup({ client, body }));
      });
    },
    {
      body: t.Object({
        title: t.String(),
        typeValue: t.Numeric(),
        description: t.String(),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
      }),
    },
  )
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await releaseGroupServiceSearchReleaseGroups({
            client,
            query: {
              title: query.title || undefined,
              type: query.type as ReleaseGroupTypeValue | undefined,
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
        type: t.Optional(t.Union([t.Literal('1'), t.Literal('2'), t.Literal('3'), t.Literal('99')])),
        is_display: t.Optional(t.Boolean()),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:releaseGroupId',
    async ({ params: { releaseGroupId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await releaseGroupServiceGetReleaseGroup({ client, path: { releaseGroupId } }),
        );
      });
    },
    {
      params: t.Object({
        releaseGroupId: t.String(),
      }),
    },
  )
  .put(
    '/:releaseGroupId',
    async ({ params: { releaseGroupId }, body, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await releaseGroupServiceUpdateReleaseGroup({
            client,
            path: { releaseGroupId },
            body,
          }),
        );
      });
    },
    {
      params: t.Object({
        releaseGroupId: t.String(),
      }),
      body: t.Object({
        title: t.String(),
        typeValue: t.Numeric(),
        description: t.String(),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
      }),
    },
  )
  .delete(
    '/:releaseGroupId',
    async ({ params: { releaseGroupId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await releaseGroupServiceDeleteReleaseGroup({
            client,
            path: { releaseGroupId },
          }),
        );
      });
    },
    {
      params: t.Object({
        releaseGroupId: t.String(),
      }),
    },
  );
