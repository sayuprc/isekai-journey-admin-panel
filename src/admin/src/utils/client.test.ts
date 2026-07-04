import { describe, expect, it, mock } from 'bun:test';

mock.module('astro:env/client', () => ({
  PUBLIC_APP_URL: 'http://admin.local',
}));

let uploadBody: unknown;

mock.module('@elysiajs/eden', () => ({
  treaty: () => ({
    api: {
      releases: {
        'jacket-art': {
          post: async (body: unknown) => {
            uploadBody = body;

            return {
              data: { jacketArtUrl: 'http://assets.local/jacket.png' },
              status: 200,
            };
          },
        },
      },
    },
  }),
}));

const { uploadReleaseJacketArt } = await import('./client');

describe('uploadReleaseJacketArt', () => {
  it('eden が multipart に変換できる形でファイルを渡す', async () => {
    const file = new File(['image'], 'jacket.png', { type: 'image/png' });

    const result = await uploadReleaseJacketArt(file);

    expect(result).toEqual({
      data: { jacketArtUrl: 'http://assets.local/jacket.png' },
      status: 200,
    });
    expect(uploadBody).toBeObject();
    expect((uploadBody as { jacketArt?: unknown }).jacketArt).toBe(file);
  });
});
