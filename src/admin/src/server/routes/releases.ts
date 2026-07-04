import { Elysia, t } from 'elysia';
import {
  releaseServiceCreateRelease,
  releaseServiceDeleteRelease,
  releaseServiceGetRelease,
  releaseServiceUploadJacketArt,
  releaseServiceUpdateRelease,
} from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const NullableStringSchema = t.Union([t.String(), t.Null()]);

const mediaSchema = t.Array(
  t.Object({
    position: t.Number(),
    formatValue: t.Numeric(),
    tracks: t.Array(
      t.Object({
        songId: t.String(),
        trackNo: t.Number(),
      }),
    ),
  }),
);

export const releases = new Elysia({ prefix: '/releases' })
  .use(authGuard)
  .post('/jacket-art', async ({ request, set, authSession }) => {
    const formData = await request.formData();
    const file = formData.get('jacketArt');

    if (!(file instanceof File)) {
      set.status = 422;
      return { errors: [{ field: 'jacketArt', message: '画像ファイルは必須です' }] };
    }

    const content = await file.arrayBuffer();

    return withAuthRetry(authSession, async (client) => {
      const result = await releaseServiceUploadJacketArt({
        client,
        body: content,
        bodySerializer: null,
        headers: {
          'Content-Type': file.type,
        },
      });

      if (!result.response) {
        throw new Error('リリースジャケットアートアップロードAPIのレスポンスを取得できませんでした');
      }

      return resolveApiResponse({ ...result, response: result.response });
    });
  })
  .post(
    '/',
    async ({ body, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseServiceCreateRelease({ client, body }));
      });
    },
    {
      body: t.Object({
        releaseGroupId: t.String(),
        name: t.String(),
        releasedOn: t.String(),
        description: t.String(),
        jacketArtUrl: NullableStringSchema,
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        media: mediaSchema,
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
        return resolveApiResponse(
          await releaseServiceUpdateRelease({
            client,
            path: { releaseId },
            body,
          }),
        );
      });
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        releasedOn: t.String(),
        description: t.String(),
        jacketArtUrl: NullableStringSchema,
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        media: mediaSchema,
      }),
    },
  )
  .delete(
    '/:releaseId',
    async ({ params: { releaseId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await releaseServiceDeleteRelease({
            client,
            path: { releaseId },
          }),
        );
      });
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
    },
  );
